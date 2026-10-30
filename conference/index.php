<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_login();

$me = current_user();
$canCreate = in_array($me['role_key'] ?? '', ['super_admin', 'admin', 'hospital_admin'], true);

/* ── JSON endpoints for chat polling ─────────────────────────────────── */
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    $roomId = filter_var($_GET['room'] ?? '', FILTER_VALIDATE_INT);
    // verify participant
    $check = $pdo->prepare('SELECT 1 FROM conference_participants WHERE room_id=:r AND user_id=:u');
    $check->execute(['r' => $roomId, 'u' => $me['id']]);
    if (!$check->fetch()) { echo json_encode([]); exit; }

    if ($_GET['api'] === 'messages') {
        $since = filter_var($_GET['since'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $q = $pdo->prepare(
            'SELECT m.id, m.message, m.sent_at, u.full_name
             FROM conference_messages m JOIN users u ON u.id=m.user_id
             WHERE m.room_id=:r AND m.id>:since ORDER BY m.id ASC LIMIT 50'
        );
        $q->execute(['r' => $roomId, 'since' => $since]);
        echo json_encode($q->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }
    echo json_encode([]); exit;
}

/* ── POST actions ─────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    /* Create room */
    if ($action === 'create') {
        if (!$canCreate) { http_response_code(403); exit('No permission.'); }
        $title    = trim((string) ($_POST['title'] ?? ''));
        $type     = (string) ($_POST['type'] ?? 'chat');
        $inviteAll = !empty($_POST['invite_all']);
        $invitees  = array_filter(array_map('intval', (array) ($_POST['invitees'] ?? [])));
        if ($title === '' || strlen($title) > 160 || !in_array($type, ['chat', 'video'], true)) {
            set_flash('danger', 'Enter a room title and choose a type.');
            redirect('/hospital%20management/conference/index.php');
        }
        $code = bin2hex(random_bytes(8)); // unique room code
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO conference_rooms (title, type, room_code, created_by) VALUES (:title,:type,:code,:user)')
            ->execute(['title' => $title, 'type' => $type, 'code' => $code, 'user' => $me['id']]);
        $roomId = (int) $pdo->lastInsertId();
        // always add creator
        $pdo->prepare('INSERT IGNORE INTO conference_participants (room_id, user_id) VALUES (:r,:u)')
            ->execute(['r' => $roomId, 'u' => $me['id']]);
        if ($inviteAll) {
            $staff = $pdo->query("SELECT u.id FROM users u JOIN roles r ON r.id=u.role_id WHERE u.is_active=1 AND r.role_key<>'patient_portal'")->fetchAll();
            $ins = $pdo->prepare('INSERT IGNORE INTO conference_participants (room_id, user_id) VALUES (:r,:u)');
            foreach ($staff as $s) { $ins->execute(['r' => $roomId, 'u' => $s['id']]); }
        } elseif ($invitees) {
            $ins = $pdo->prepare('INSERT IGNORE INTO conference_participants (room_id, user_id) VALUES (:r,:u)');
            foreach ($invitees as $uid) { $ins->execute(['r' => $roomId, 'u' => $uid]); }
        }
        record_audit('create', 'conference_room', (string) $roomId, $type . ' room created: ' . $title);
        // notify participants (excluding self)
        $parts = $pdo->prepare('SELECT user_id FROM conference_participants WHERE room_id=:r AND user_id<>:u');
        $parts->execute(['r' => $roomId, 'u' => $me['id']]);
        $notifIns = $pdo->prepare('INSERT INTO notifications (user_id,action,entity_type,entity_id,message) VALUES (:uid,:action,:et,:eid,:msg)');
        foreach ($parts->fetchAll() as $p) {
            $notifIns->execute(['uid' => $p['user_id'], 'action' => 'conference_invite', 'et' => 'conference_room', 'eid' => (string)$roomId, 'msg' => $me['full_name'] . ' invited you to a ' . $type . ' conference: ' . $title]);
        }
        $pdo->commit();
        set_flash('success', 'Conference room created.');
        redirect('/hospital%20management/conference/index.php?room=' . $roomId);
    }

    /* Send chat message */
    if ($action === 'message') {
        $roomId  = filter_var($_POST['room_id'] ?? '', FILTER_VALIDATE_INT);
        $message = trim((string) ($_POST['message'] ?? ''));
        if (!$roomId || $message === '' || strlen($message) > 2000) {
            http_response_code(400); exit;
        }
        $check = $pdo->prepare('SELECT 1 FROM conference_participants WHERE room_id=:r AND user_id=:u');
        $check->execute(['r' => $roomId, 'u' => $me['id']]);
        if (!$check->fetch()) { http_response_code(403); exit; }
        $pdo->prepare('INSERT INTO conference_messages (room_id, user_id, message) VALUES (:r,:u,:m)')
            ->execute(['r' => $roomId, 'u' => $me['id'], 'm' => $message]);
        // return new message id for polling cursor
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['id' => (int) $pdo->lastInsertId()]);
        exit;
    }

    /* Close room */
    if ($action === 'close') {
        if (!$canCreate) { http_response_code(403); exit; }
        $roomId = filter_var($_POST['room_id'] ?? '', FILTER_VALIDATE_INT);
        $pdo->prepare('UPDATE conference_rooms SET is_active=0 WHERE id=:id AND created_by=:u')
            ->execute(['id' => $roomId, 'u' => $me['id']]);
        record_audit('close', 'conference_room', (string) $roomId, 'Conference room closed');
        set_flash('success', 'Room closed.');
        redirect('/hospital%20management/conference/index.php');
    }

    redirect('/hospital%20management/conference/index.php');
}

/* ── Load data ────────────────────────────────────────────────────────── */
$activeRoomId = filter_var($_GET['room'] ?? '', FILTER_VALIDATE_INT) ?: null;
$activeRoom   = null;
if ($activeRoomId) {
    $q = $pdo->prepare(
        'SELECT r.*, u.full_name AS creator_name FROM conference_rooms r
         JOIN users u ON u.id=r.created_by
         JOIN conference_participants cp ON cp.room_id=r.id AND cp.user_id=:uid
         WHERE r.id=:rid'
    );
    $q->execute(['uid' => $me['id'], 'rid' => $activeRoomId]);
    $activeRoom = $q->fetch() ?: null;
}

// rooms this user is part of
$myRooms = $pdo->prepare(
    'SELECT r.id, r.title, r.type, r.room_code, r.is_active, r.created_at, u.full_name AS creator_name,
            (SELECT COUNT(*) FROM conference_participants WHERE room_id=r.id) AS participant_count,
            (SELECT COUNT(*) FROM conference_messages WHERE room_id=r.id) AS message_count
     FROM conference_rooms r
     JOIN conference_participants cp ON cp.room_id=r.id AND cp.user_id=:uid
     JOIN users u ON u.id=r.created_by
     ORDER BY r.is_active DESC, r.created_at DESC LIMIT 50'
);
$myRooms->execute(['uid' => $me['id']]);
$myRooms = $myRooms->fetchAll();

// staff list for invite picker
$staff = $canCreate ? $pdo->query(
    "SELECT u.id, u.full_name, r.name AS role_name FROM users u JOIN roles r ON r.id=u.role_id
     WHERE u.is_active=1 AND r.role_key<>'patient_portal' ORDER BY u.full_name"
)->fetchAll() : [];

// initial messages for active room
$initMessages = [];
if ($activeRoom) {
    $q = $pdo->prepare(
        'SELECT m.id, m.message, m.sent_at, u.full_name
         FROM conference_messages m JOIN users u ON u.id=m.user_id
         WHERE m.room_id=:r ORDER BY m.id ASC LIMIT 100'
    );
    $q->execute(['r' => $activeRoomId]);
    $initMessages = $q->fetchAll();
}

$pageTitle = 'Conference';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2>Conference</h2><p>Start a video call or group chat with selected staff or the whole team.</p></div>
</div>
<div class="row g-3">
    <!-- Left: room list + create -->
    <div class="col-12 col-md-4">
        <?php if ($canCreate): ?>
        <section class="content-card form-card mt-0">
            <div class="card-heading"><h3>New room</h3></div>
            <form method="post" id="createRoomForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input class="form-control" name="title" maxlength="160" required placeholder="e.g. Morning briefing">
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <div class="d-flex gap-3">
                        <label class="d-flex align-items-center gap-2"><input type="radio" name="type" value="chat" checked> Chat</label>
                        <label class="d-flex align-items-center gap-2"><input type="radio" name="type" value="video"> Video call</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Invite</label>
                    <div class="mb-2"><label class="d-flex align-items-center gap-2"><input type="checkbox" name="invite_all" id="inviteAll" value="1"> All active staff</label></div>
                    <div id="inviteePicker">
                        <select class="form-select" name="invitees[]" multiple size="5">
                            <?php foreach ($staff as $s): ?>
                                <?php if ((int)$s['id'] !== (int)$me['id']): ?>
                                <option value="<?= (int)$s['id'] ?>"><?= e($s['full_name'] . ' · ' . $s['role_name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hold Ctrl / Cmd to select multiple.</div>
                    </div>
                </div>
                <button class="btn btn-primary w-100" type="submit">Create room</button>
            </form>
        </section>
        <?php endif; ?>
        <section class="content-card mt-0 <?= $canCreate ? '' : 'mt-0' ?>">
            <div class="card-heading"><h3>My rooms</h3></div>
            <?php if (!$myRooms): ?>
                <div class="notif-empty">No conference rooms yet.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                <?php foreach ($myRooms as $room): ?>
                    <a href="/hospital%20management/conference/index.php?room=<?= (int)$room['id'] ?>"
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-start py-2 px-3 <?= (int)($activeRoom['id'] ?? 0) === (int)$room['id'] ? 'active' : '' ?>">
                        <div>
                            <div class="fw-semibold" style="font-size:13px"><?= e($room['title']) ?></div>
                            <small class="text-muted"><?= e(ucfirst($room['type'])) ?> &middot; <?= (int)$room['participant_count'] ?> members</small>
                        </div>
                        <span class="badge-<?= $room['is_active'] ? 'soft' : 'neutral' ?>" style="flex-shrink:0"><?= $room['is_active'] ? 'Open' : 'Closed' ?></span>
                    </a>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- Right: active room -->
    <div class="col-12 col-md-8">
        <?php if (!$activeRoom): ?>
            <div class="content-card mt-0 d-flex align-items-center justify-content-center" style="min-height:320px">
                <p class="text-muted">Select a room or create one to start.</p>
            </div>
        <?php else: ?>
        <section class="content-card mt-0">
            <div class="card-heading">
                <div>
                    <h3><?= e($activeRoom['title']) ?></h3>
                    <small class="text-muted"><?= e(ucfirst($activeRoom['type'])) ?> &middot; Created by <?= e($activeRoom['creator_name']) ?></small>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($activeRoom['type'] === 'video' && $activeRoom['is_active']): ?>
                        <button class="btn btn-sm btn-primary" onclick="joinVideo()">Join video call</button>
                    <?php endif; ?>
                    <?php if ($canCreate && (int)$activeRoom['created_by'] === (int)$me['id'] && $activeRoom['is_active']): ?>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="close">
                            <input type="hidden" name="room_id" value="<?= (int)$activeRoom['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Close this room?')">Close room</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($activeRoom['type'] === 'video'): ?>
            <!-- Jitsi video embed (hidden until joined) -->
            <div id="videoContainer" style="display:none;height:480px;background:#111;border-radius:0">
                <div id="jitsiFrame" style="width:100%;height:100%"></div>
            </div>
            <?php endif; ?>

            <!-- Chat -->
            <div id="chatBox" style="height:340px;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:8px">
                <?php foreach ($initMessages as $msg): ?>
                <div class="conf-msg <?= (int)$msg['user_id'] ?? 0 === (int)$me['id'] ? 'conf-msg-self' : '' ?>">
                    <span class="conf-msg-name"><?= e($msg['full_name']) ?></span>
                    <span class="conf-msg-time"><?= e(date('g:i a', strtotime($msg['sent_at']))) ?></span>
                    <div class="conf-msg-body"><?= nl2br(e($msg['message'])) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($activeRoom['is_active']): ?>
            <div style="border-top:1px solid var(--line);padding:12px 16px">
                <form id="chatForm" class="d-flex gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="message">
                    <input type="hidden" name="room_id" value="<?= (int)$activeRoom['id'] ?>">
                    <input class="form-control" id="chatInput" name="message" maxlength="2000" placeholder="Type a message..." autocomplete="off" required>
                    <button class="btn btn-primary" type="submit">Send</button>
                </form>
            </div>
            <?php else: ?>
            <div class="notif-empty">This room is closed.</div>
            <?php endif; ?>
        </section>
        <?php endif; ?>
    </div>
</div>

<?php if ($activeRoom && $activeRoom['type'] === 'video'): ?>
<script src="https://meet.jit.si/external_api.js"></script>
<?php endif; ?>
<script>
document.getElementById('inviteAll')?.addEventListener('change', function(){
    document.getElementById('inviteePicker').style.display = this.checked ? 'none' : '';
});

<?php if ($activeRoom): ?>
var ROOM_ID = <?= (int)$activeRoom['id'] ?>;
var MY_NAME = <?= json_encode($me['full_name']) ?>;
var ROOM_CODE = <?= json_encode($activeRoom['room_code']) ?>;
var lastId = <?= $initMessages ? (int)end($initMessages)['id'] : 0 ?>;
var chatBox = document.getElementById('chatBox');

function scrollChat(){ chatBox.scrollTop = chatBox.scrollHeight; }
scrollChat();

function appendMsg(name, text, time, isSelf) {
    var d = document.createElement('div');
    d.className = 'conf-msg' + (isSelf ? ' conf-msg-self' : '');
    d.innerHTML = '<span class="conf-msg-name">' + name.replace(/</g,'&lt;') + '</span>'
        + '<span class="conf-msg-time">' + time + '</span>'
        + '<div class="conf-msg-body">' + text.replace(/</g,'&lt;').replace(/\n/g,'<br>') + '</div>';
    chatBox.appendChild(d);
    scrollChat();
}

<?php if ($activeRoom['is_active']): ?>
// Poll for new messages every 3 s
setInterval(function(){
    fetch('/hospital%20management/conference/index.php?api=messages&room=' + ROOM_ID + '&since=' + lastId)
        .then(function(r){ return r.json(); })
        .then(function(msgs){
            msgs.forEach(function(m){
                appendMsg(m.full_name, m.message, new Date(m.sent_at).toLocaleTimeString([],{hour:'numeric',minute:'2-digit'}), false);
                lastId = m.id;
            });
        });
}, 3000);

document.getElementById('chatForm').addEventListener('submit', function(e){
    e.preventDefault();
    var input = document.getElementById('chatInput');
    var msg = input.value.trim();
    if (!msg) return;
    var fd = new FormData(this);
    fetch('/hospital%20management/conference/index.php', { method:'POST', body:fd, redirect:'error' })
        .then(function(r){ return r.json(); })
        .then(function(data){
            appendMsg(MY_NAME, msg, new Date().toLocaleTimeString([],{hour:'numeric',minute:'2-digit'}), true);
            if (data.id) lastId = data.id;
            input.value = '';
        }).catch(function(){});
});
<?php endif; ?>

<?php if ($activeRoom['type'] === 'video'): ?>
function joinVideo(){
    document.getElementById('videoContainer').style.display = 'block';
    var domain = 'meet.jit.si';
    var options = {
        roomName: 'kivucare-' + ROOM_CODE,
        width: '100%', height: 480,
        parentNode: document.getElementById('jitsiFrame'),
        userInfo: { displayName: MY_NAME },
        configOverwrite: { startWithAudioMuted: false },
        interfaceConfigOverwrite: { SHOW_JITSI_WATERMARK: false }
    };
    new JitsiMeetExternalAPI(domain, options);
}
<?php endif; ?>
<?php endif; ?>
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
