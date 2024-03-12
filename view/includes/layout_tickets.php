<link rel="stylesheet" type="text/css" href="view/includes/styles_map.css?v=1.5" />
<div class="mod_wrapper">
  <div class="mod_header">
    <div class="mod_title_header">
      <div>
        <span id="ticketTitle" class="mod_title_heading">New ticket</span>
        <i id="edit_ticketTitle" class="ri-edit-line" style="color: #2b6aff;"></i>
      </div>
      <div><span class="mod_title_heading" style="font-size: 16px;">Ticket ID:</span><span class="" id="md_tck_cd_ticketID"><?php echo uniqid(); ?></span></div>
      <div id="md_tck_cd_ticketID_internal" class="hidden"></div>
    </div>

    <p class="mod_title_sm_header">
      <span id="md_ticketState">Private Ticket</span>
      <button id="md_ticketStateToggle" class="btn btn-outline-primary btn-sm ml-2">Make public</button>
      <button id="md_ticketStateDone" class="ml-2 btn btn-outline-success btn-sm">Close Ticket</button>
      <button id="md_ticketStatePending" class="ml-2 btn btn-outline-success btn-sm">Set Pending</button>
      <button id="md_ticketStateProgress" class="ml-2 btn btn-outline-success btn-sm">In Bearbeitung</button>
    </p>
  </div>
  <div class="mod_body" style="flex-grow: 1;">
    <div class="row" style="height: 100%;">
      <div class="col-md-8 task-single-col-left m-0 p-2" style="display: flex; flex-direction: column; height: 100%;">
        <div class="colbox_deswrapper">
          <div id="ini" class="task_inf_descwrwapperbox">
            <div class="task_inf_bodwrapper">
              <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <a class="nav-link active" id="tickettab_Description" data-bs-toggle="tab" href="#tickettab_content_description" role="tab" aria-controls="tickettab_content_description" aria-selected="true">Ticket History</a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link hidden" id="tickettab_initDescription" data-bs-toggle="tab" href="#tickettab_initDescription_content" role="tab" aria-controls="tickettab_initDescription_content" aria-selected="false">Customer Timeline</a>
                </li>
              </ul>
              <div class="tab-content mt-2" style="padding: 10px 5px !important;">
                <div class="tab-pane fade show active d-flex flex-column" style="height: 100%;" id="tickettab_content_description" role="tabpanel" aria-labelledby="tickettab_Description">
                  <div id="task_inf_ticketHistoryWrapper"></div>
                  <div id="task_inf_ticketContent" class="form-control"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4 task-single-col-right">
        <p class="task_inf_createdby">Created by <?php echo $currentuser ?></p>
        <p class="task_inf_createdat"><?php echo date('Y-m-d') ?> - </p>
        <div class="task_info_blockstatus">
          <i class="ri-flag-line"></i>
          <span class="tast_info_binfotextheading">Status:</span>
          <span class="tast_info_binfotextvalues tck_statusfield" id="md_tck_status">new</span>
        </div>
        <div class="task_info_blockstatus d-flex align-items-center">
          <i class="ri-briefcase-4-line mr-2"></i>
          <span class="tast_info_binfotextheading mr-2">Goal:</span>
          <div class="dropdown">
            <button class="btn btn-secondary btn-sm dropdown-toggle task-dropdown-button" style="padding: 0px 6px;" type="button" id="md_tck_goal" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              Select a Goal
            </button>
            <div class="dropdown-menu task-dropdown-menu" aria-labelledby="md_tck_goal">
              <a class="dropdown-item task-dropdown-item" href="#" data-value="HB2">New HBG needed</a>
              <a class="dropdown-item task-dropdown-item" href="#" data-value="CNF">Confirmation needed</a>
              <a class="dropdown-item task-dropdown-item" href="#" data-value="CLR">Not clear</a>
              <a class="dropdown-item task-dropdown-item" href="#" data-value="HCN">HBG Correction</a>
              <a class="dropdown-item task-dropdown-item" href="#" data-value="HQS">HBG questions</a>
              <a class="dropdown-item task-dropdown-item" href="#" data-value="UNSET">- Unset -</a>
            </div>
          </div>
        </div>
        <div class="task_info_blockstatus">
          <i class="ri-calendar-line"></i>
          <span class="tast_info_binfotextheading">Start Date:</span>
          <span class="tast_info_binfotextvalues" id="md_tck_datestart"><?php echo date('Y-m-d') ?></span>
        </div>
        <div class="task_info_blockstatus">
          <i class="ri-calendar-event-line"></i>
          <span class="tast_info_binfotextheading">Last Update:</span>
          <span class="tast_info_binfotextvalues" id="md_tck_dateedit"><?php echo date('Y-m-d') ?></span>
        </div>
        <div class="task_info_blockstatus">
          <i class="ri-calendar-check-line"></i>
          <span class="tast_info_binfotextheading">End Date:</span>
          <span class="tast_info_binfotextvalues" id="md_tck_dateend"> - </span>
        </div>
        <div class="task_info_blockstatus d-flex align-items-center">
          <i class="ri-flashlight-line mr-2"></i>
          <span class="tast_info_binfotextheading mr-2">Priority:</span>
          <div class="dropdown">
            <button class="btn btn-sm dropdown-toggle task-dropdown-button" style="padding: 0px 6px;" type="button" id="md_tck_prio" data-toggle="dropdown" aria-expanded="false" style="background-color: #e5cc1c;">
              Low - 3
            </button>
            <div class="dropdown-menu task-dropdown-menu" aria-labelledby="md_tck_prio">
              <a class="dropdown-item task-dropdown-item" href="#" data-value="High" data-color="#e52f1c">High - 1</a>
              <a class="dropdown-item task-dropdown-item" href="#" data-value="Normal" data-color="#e58b1c">Normal - 2</a>
              <a class="dropdown-item task-dropdown-item" href="#" data-value="Low" data-color="#e5cc1c">Low - 3</a>
            </div>
          </div>
        </div>

        <div class="task_info_blockstatus file_section">
          <i class="ri-file-line"></i>
          <span class="task_info_infoheading">Attached Files:</span>
          <div class="file_list">
            <!-- Existing file items go here -->
          </div>
        </div>
        <div class="task_info_blockstatus image_section">
          <i class="ri-file-line"></i>
          <span class="task_info_infoheading">Attached Images:</span>
          <div class="image_list">
            <!-- Existing image items go here -->
          </div>
        </div>
        <div class="task_info_blockstatus">
          <div id="dropArea">
            <input type="file" id="fileInput" multiple style="display: none" />
            <label for="fileInput" class="dropAreaLabel"> Drag & Drop Files Here
              <br />
              or
              <br />Choose File</label>
          </div>
        </div>

        <div class="linespacer"></div>
        <div class="task_info_blockstatus">
          <h6>
            <i class="ri-home-3-line"></i>
            <span class="tast_info_binfotextheadingEx">Customer Data</span>
          </h6>
        </div>
        <div class="task_info_blockstatus">
          <b><span class="tast_info_binfotexsm">Homeid: </span></b>
          <span class="tast_info_binfotexsm" id="md_tck_cd_homeid">ARP005055511</span>
        </div>
        <div class="task_info_blockstatus">
          <b><span class="tast_info_binfotexsm">Name: </span></b>
          <span class="tast_info_binfotexsm" id="md_tck_cd_name">Alexander Dobrinzgi</span>
        </div>
        <div class="task_info_blockstatus">
          <b><span class="tast_info_binfotexsm">Address: </span></b>
          <span class="tast_info_binfotexsm" id="md_tck_cd_address">Alter Ankerstr 32, 59194 Dortmund</span>
        </div>
        <div class="task_info_blockstatus">
          <b><span class="tast_info_binfotexsm">Phone1: </span></b>
          <span class="tast_info_binfotexsm" id="md_tck_cd_phone1">+49 157 2364418</span>
        </div>
        <div class="task_info_blockstatus">
          <b><span class="tast_info_binfotexsm">Phone2: </span></b>
          <span class="tast_info_binfotexsm" id="md_tck_cd_phone2">+49 157 2364418</span>
        </div>
        <div class="task_info_blockstatus">
          <b><span class="tast_info_binfotexsm">Mail: </span></b>
          <span class="tast_info_binfotexsm" id="md_tck_cd_mail">+49 157 2364418</span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    const dropArea = $('#dropArea');

    dropArea.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
      e.preventDefault();
      e.stopPropagation();
    });

    dropArea.on('dragover dragenter', function() {
      dropArea.addClass('dragover');
    });

    dropArea.on('dragleave dragend drop', function() {
      dropArea.removeClass('dragover');
    });

    dropArea.on('drop', function(e) {
      let files = e.originalEvent.dataTransfer.files;
      handleFiles(files);
    });

    $('#fileInput').on('change', function(e) {
      let files = e.target.files;
      handleFiles(files);
    });

    function handleFiles(files) {
      for (let file of files) {
        uploadFile(file);
      }
    }

    function uploadFile(file) {
      // Replace spaces with underscores in the filename
      const sanitizedFilename = file.name.replace(/\s+/g, '');

      let fileSizeInMB = (file.size / (1024 * 1024)).toFixed(2);
      let uploadDiv = $('<div class="uploadDiv"></div>');
      uploadDiv.append(`<div class="uploadText"><span class="float-start">${sanitizedFilename}</span><span class="float-end" style="font-size:13px;">(${fileSizeInMB} MB)</span></div>`);

      let progressContainer = $('<div class="progress"></div>');
      let progressBar = $('<div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>');
      progressContainer.append(progressBar);
      uploadDiv.append(progressContainer);
      dropArea.append(uploadDiv);

      const formData = new FormData();
      formData.append('func', 'uploadFile');
      formData.append('file_homeid', $('#md_tck_cd_homeid').text());
      formData.append('file[]', new File([file], sanitizedFilename, {
        type: file.type
      })); // Use sanitized filename here


      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'view/load/tickets_load.php', true); // true for asynchronous

      xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
          const percentComplete = ((e.loaded / e.total) * 100).toFixed(0);
          progressBar.css('width', percentComplete + '%').attr('aria-valuenow', percentComplete).text(percentComplete + '%');

          if (percentComplete === '100') {
            progressBar.addClass('bg-success');
            uploadDiv.find('.float-start').prepend('<span class="checkmark">&#10003; </span>');
            setTimeout(function() {
              uploadDiv.fadeOut(2000, function() {
                $(this).remove();
              });
            }, 500);
          }
        }
      });

      xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
          let serverResponse;
          try {
            if (xhr.responseText) {
              serverResponse = JSON.parse(xhr.responseText);
              console.log('serverResponse', xhr.responseText)
            } else {
              throw new Error('Empty response from the server');
              console.log('Empty response from the server');
            }
          } catch (e) {
            console.error('Error parsing JSON response:', e);
            return;
          }

          console.log(serverResponse);

          if (serverResponse.message && serverResponse.message.length > 0) {
            serverResponse.message.forEach((fileInfo) => {
              const fileType = fileInfo.filename.split('.').pop().toLowerCase();
              const isImage = ['jpg', 'jpeg', 'png'].includes(fileType);
              const today = new Date().toISOString().split('T')[0];
              const filePath = fileInfo.filepath.replace('/var/www/html', 'https://crm.scan4-gmbh.de');


              const fileItemContent = `
            <span class="task_listfiledetails">${today}</span>
            <span class="task_info_binfotextvalues fileitem">${fileInfo.filename}</span>
            <span class="task_editfile" style="display:none;"><i class="ri-edit-line" style="color: #2b6aff;"></i></span>
            <span class="task_deletefile"><i class="ri-delete-bin-line"></i></span>
            `;

              let fileItem;
              if (isImage) {
                fileItem = `
                <div class="task_listfileitem" data-file-id="${fileInfo.id}" style="cursor:pointer;">
                    <a data-fancybox="gallery" data-src="${filePath}">
                        ${fileItemContent}
                        <div class="image_preview">
                            <img src="${filePath}" alt="${fileInfo.filename}">
                        </div>
                    </a>
                </div>
            `;
              } else {
                fileItem = `
                <div class="task_listfileitem" data-file-id="${fileInfo.id}">
                    <a href="${filePath}" target="_blank">
                        ${fileItemContent}
                    </a>
                </div>
            `;
              }

              $(isImage ? '.image_section .image_list' : '.file_section .file_list').append(fileItem);
              div_GrowUp('#task_inf_ticketContent')
            });

            // Bind FancyBox to the dynamically created elements
            Fancybox.bind('[data-fancybox="gallery"]', {});
          }

        }
      };


      xhr.send(formData);
    }
    //______________________________________________________________________________//
    // display image preview
    $(document).on('mouseenter', '.task_listfileitem', function() {
      $(this).find('.image_preview').show();
    }).on('mouseleave', '.task_listfileitem', function() {
      $(this).find('.image_preview').hide();
    });
    //______________________________________________________________________________//
    // let the title be editable
    $('#edit_ticketTitle').click(function() {
      var span = $('#ticketTitle');
      span.attr('contenteditable', 'true');
      span.addClass('form-control');
      span.focus();
    });

    $('#ticketTitle').on('blur', function() {
      $(this).removeAttr('contenteditable');
      $(this).removeClass('form-control');
    });
    //______________________________________________________________________________//
    // update dropwonws
    $(".task-dropdown-item").click(function(event) {
      event.preventDefault(); // Prevent default anchor click behavior

      // Get parent dropdown's aria-labelledby
      var dropdownId = $(this).parent().attr("aria-labelledby");

      // Get selected value and text
      var selectedValue = $(this).data("value") || $(this).text();
      var selectedText = $(this).text();

      // Update the button text based on dropdownId
      $("#" + dropdownId).text(selectedText);

      // Update the title heading text if the Goal dropdown is selected
      if (dropdownId === "md_tck_goal") {
        $("#ticketTitle").text(selectedText);
      }

      // Store the value to data-value attribute
      $("#" + dropdownId).data("value", selectedValue);

      // Update background color if data-color is present
      if ($(this).data("color")) {
        $("#" + dropdownId).css("background-color", $(this).data("color"));
      }
    });

    // Function to fetch stored values later
    function fetchStoredValue(dropdownId) {
      var storedValue = $("#" + dropdownId).data("value");
      console.log("Stored Value for " + dropdownId + ":", storedValue);
    }
    // preselect prio item 3
    $(".dropdown-menu[aria-labelledby='md_tck_prio'] .dropdown-item[data-value='3']").trigger('click');

    //______________________________________________________________________________//
    // delete file from server
    $(document).on('click', '.task_deletefile', function() {
      const fileId = $(this).closest('.task_listfileitem').data('file-id');
      const $this = $(this); // Preserve the context

      $.ajax({
          url: 'view/load/tickets_load.php',
          method: 'POST',
          data: {
            func: 'ticket_fileDelete',
            file_id: fileId
          },
          dataType: 'json'
        })
        .done(function(response) {
          console.log(response)
          if (response.success) {
            console.log(response.message);
            $this.closest('.task_listfileitem').remove(); // Use $this here
          } else {
            console.log(response.message); // Display error message
          }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
          console.log('jqXHR:', jqXHR);
          console.log('textStatus:', textStatus);
          console.log('errorThrown:', errorThrown);
          alert('An error occurred: ' + textStatus); // Handle any AJAX errors
        })
        .always(function() {
          // This will always be executed, regardless of whether the request was successful or not
          console.log('AJAX request completed');
        });
    });




  });
</script>


<style>

</style>