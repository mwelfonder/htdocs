<script src="https://jssip.net/download/releases/jssip-3.4.2.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<div id="errorMessage">must set sip uri/password</div>

<div id="menu" style="display: none;">
  <!--------  <button class="menu-button" id="settings">Einstellungen</button>---->
    <button class="menu-button active" id="history"><i class="bi bi-clock-history"></i> Verlauf</button>
    <div id="menu-content">
        <div id="menu-text">
            <i class="ri-ghost-line"></i>
            <p>Es sind noch keine Einstellungen vorhanden.</p>
        </div>
    </div>
</div>
<div id="wrapper" class="sipGateWrapper">
    <div id="toggleMenu"><i class="bi bi-sliders"></i></i></div>
    <div id="incomingCall" style="display: none">
        <div class="callInfo">
            <h3>Anruf</h3>
            <div id="copiedGif" style="display: none;">
                <img src="/view/images/anim_check_splash.gif" alt="Kopiert">
            </div>
            <p id="incomingCallNumber">Unbekannt</p>
        </div>
        <div id="answer"> <i class="fa fa-phone"></i></div>
        <div id="reject"> <i class="fa fa-phone"></i></div>
    </div>
    <div id="callStatus" style="display: none">
        <div class="callInfo">
            <h3 id="callInfoText">info text goes here</h3>
            <p id="callInfoNumber">info number goes here</p>
        </div>
        <div id="hangUp"> <i class="fa fa-phone"></i>
        </div>
    </div>
    <!---------TO FIELD---------------------------------------------------->
    <!---------DIALPAD---------------------------------------------------->
    <div id="inCallButtons" style="display: none">
        <div id="dialPad">

            <div class="dialpad-char" data-value="1" unselectable="on">1</div>
            <div class="dialpad-char" data-value="2" unselectable="on">2</div>
            <div class="dialpad-char" data-value="3" unselectable="on">3</div>
            <div class="dialpad-char" data-value="4" unselectable="on">4</div>
            <div class="dialpad-char" data-value="5" unselectable="on">5</div>
            <div class="dialpad-char" data-value="6" unselectable="on">6</div>
            <div class="dialpad-char" data-value="7" unselectable="on">7</div>
            <div class="dialpad-char" data-value="8" unselectable="on">8</div>
            <div class="dialpad-char" data-value="9" unselectable="on">9</div>
            <div class="dialpad-char" data-value="*" unselectable="on">*</div>
            <div class="dialpad-char" data-value="0" unselectable="on">0</div>
            <div class="dialpad-char" data-value="#" unselectable="on">#</div>
        </div>
        <div id="mute">
            <i id="muteIcon" class="fa fa-microphone"></i>
        </div>
    </div>

    <!---------DIAL CONTROLS-------------------------------------------->
    <div id="callControl">
        <div id="to">
            <input id="toField" type="text" placeholder="Nummer eingeben" />
            <div id="connectCall"> <i class="bi bi-telephone-plus-fill"></i></div>
            <div id="muteRingtone"> <i class="bi bi-volume-down-fill"></i></i></div>
        </div>
    </div>

</div>
</div>