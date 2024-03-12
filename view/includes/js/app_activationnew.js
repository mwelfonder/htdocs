document.addEventListener('DOMContentLoaded', function () {
    var listModeBtn = document.getElementById('listMode');
    var carouselModeBtn = document.getElementById('carouselMode');
    var activateBtn = document.getElementById('activateBtn');
    var errorBtn = document.getElementById('errorBtn');
    var customDateInput = document.getElementById('customDate');
    var modal = document.getElementById('popupModal');
    var span = document.getElementsByClassName("close")[0];

    listModeBtn.addEventListener('click', function () { loadModeData('list'); });
    carouselModeBtn.addEventListener('click', function () { loadModeData('carousel'); });
    activateBtn.addEventListener('click', activateData);
    errorBtn.addEventListener('click', errorData);
    customDateInput.addEventListener('change', function () {
        updateModeSelectionEnabled(true);
        updateTermineInfo(); // Update bei Datumänderung
    });

    span.onclick = closeModal;
    window.onclick = function (event) {
        if (event.target == modal) {
            closeModal();
        }
    };

    updateTermineInfo();

    function loadModeData(mode) {
        var selectedDate = customDateInput.value || null;
        sendRequest('load', mode, selectedDate);
    }

    function activateData() {
        var uid = document.getElementById('uid').value;

        sendRequest('activate', null, null, uid, function (response) {
            showNotification("Erfolgreich freigeschaltet");
            console.log("Activated: ", uid); // Fügt die Antwort in die Konsole ein
            loadModeData('carousel');
            updateTermineInfo();
        });
    }

    function errorData() {
        var uid = document.getElementById('uid').value;

        sendRequest('activateerror', null, null, uid, function (response) {
            showNotification("Erfolgreich nicht freigeschaltet");
            console.log("Activated: ", uid); // Fügt die Antwort in die Konsole ein
            loadModeData('carousel');
        });
    }

    function sendRequest(action, mode, date, uid, callback) {
        $.ajax({
            url: 'view/load/activationnew_load.php',
            type: 'POST',
            data: { action: action, mode: mode, date: date, uid: uid },
            success: function (response) {
                if (action !== 'load_activationtracker') {
                    document.getElementById('modalData').innerHTML = response;
                    document.getElementById('popupModal').style.display = "block";
                }

                var uidElement = document.getElementById('uid');
                if (uidElement && uid) {
                    uidElement.value = uid;
                }

                if (callback) {
                    callback(response);
                }
            }
        });
    }


    function closeModal() {
        modal.style.display = "none";
    }

    function updateModeSelectionEnabled(enabled) {
        listModeBtn.disabled = !enabled;
        carouselModeBtn.disabled = !enabled;
    }

    function showNotification(message) {
        var notification = document.createElement("div");
        notification.className = "success-notification";
        notification.innerText = message;
        document.body.appendChild(notification);

        // Benachrichtigung nach kurzer Zeit automatisch ausblenden
        setTimeout(function () {
            document.body.removeChild(notification);
        }, 3000); // 3 Sekunden bis zum Ausblenden
    }
    function updateTermineInfo() {
        var selectedDate = document.getElementById('customDate').value || null;
        sendRequest('load_activationtracker', null, selectedDate, null, function (response) {
            console.log("Activated: ", response); // Fügt die Antwort in die Konsole ein
            document.getElementById('activationTracker').innerText = response.trim() ? response : "0 von 0";
        });
    }
    
});

function copyToClipboard(text) {
  const el = document.createElement('textarea');
  el.value = text;
  document.body.appendChild(el);
  el.select();
  document.execCommand('copy');
  document.body.removeChild(el);

}