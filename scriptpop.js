const openModalButtons = document.querySelectorAll('[data-modal-target]');
const closeModalButtons = document.querySelectorAll('[data-close-button]');
const overlay = document.getElementById('overlay');

openModalButtons.forEach(button => {
  button.addEventListener('click', () => {
    const modal = document.querySelector(button.dataset.modalTarget);
    const userId = button.dataset.id; // Get the row ID (if available)

    if (modal) {
      // Open the update modal and pass the user ID to the function
      if (modal.id === 'updateModal' && userId) {
        openUpdateModal(modal, userId);
      }
      // Open the view record modal and pass the user ID to the function
      else if (modal.id === 'viewrecord' && userId) {
        openViewModal(modal, userId);
      }
      // Open the user account or password update form modals and pass the user ID
      else if ((modal.id === 'updateAccForm' || modal.id === 'updatePassForm') && userId) {
        openUpdateUser(modal, userId);
      } 
      // Fallback: open the modal without specific data if no matching cases
      else {
        openModal(modal);
      }
    } else {
      console.error('Modal not found:', button.dataset.modalTarget);
    }
  });
});


overlay.addEventListener('click', () => {
  const modals = document.querySelectorAll('.modal.active');
  modals.forEach(modal => {
    closeModal(modal);
  });
});
overlay.addEventListener('click', () => {
  const modals = document.querySelectorAll('.pmodal.active');
  modals.forEach(modal => {
    closeModal(modal);
  });
});

closeModalButtons.forEach(button => {
  button.addEventListener('click', () => {
    const modal = button.closest('.modal');
    closeModal(modal);
  });
});

function openModal(modal) {
  if (modal == null) return;
  modal.classList.add('active');
  overlay.classList.add('active');
}

function closeModal(modal) {
  if (modal == null) return;
  modal.classList.remove('active');
  overlay.classList.remove('active');
}
function openViewModal(modal, userId) {
  if (modal == null || userId == null) return;
  modal.classList.add('active');
  overlay.classList.add('active');

  // Fetch data using the userId and populate the modal
  fetch('?action=view&id=' + userId)
    .then(response => response.json())
    .then(data => {
      if (data && Object.keys(data).length > 0) {
        
        document.getElementById('vunique_id').value  = data.unique_id || 'N/A';
        document.getElementById('vpicture').src = data.picture || 'N/A';
        document.getElementById('vcourse').textContent  = data.course || 'N/A';
        document.getElementById('vyear').textContent  = data.year_level || 'N/A';
        document.getElementById('vsection').textContent  = data.section || 'N/A';
        document.getElementById('vlname').textContent  = data.lname || 'N/A';
        document.getElementById('vfname').textContent  = data.fname || 'N/A';
        document.getElementById('vmname').textContent  = data.mname || 'N/A';
        document.getElementById('vgender').textContent  = data.gender || 'N/A';
        document.getElementById('vreligion').textContent  = data.religion || 'N/A';
        document.getElementById('vaddress').textContent  = data.address || 'N/A';
        document.getElementById('vbday').textContent  = data.bday || 'N/A';
        document.getElementById('vguardian').textContent  = data.guardian || 'N/A';
        document.getElementById('vage').textContent  = data.age || 'N/A';
        document.getElementById('vemergency').textContent  = data.emergency_no || 'N/A';
          document.getElementById('vallergy').textContent  = data.allergy || 'N/A';
          document.getElementById('vothers').textContent  = data.others || 'N/A';
          document.getElementById('vasthma').textContent  = data.asthma || 'N/A';
          document.getElementById('vmedication').textContent  = data.medication || 'N/A';
          document.getElementById('vdiabetes').textContent  = data.diabetes || 'N/A';
          document.getElementById('vheartdisease').textContent  = data.heartdisease || 'N/A';
          document.getElementById('vcovidvax').textContent  = data.vaccine || 'N/A';
          document.getElementById('vseizure').textContent  = data.seizure || 'N/A';
          document.getElementById('vcvaxstatus').textContent  = data.vaccine_status || 'N/A';
          document.getElementById('vdateOfexamination').textContent  = data.dOexamination || 'N/A';
          document.getElementById('vheight').textContent  = data.height || 'N/A';
          document.getElementById('vweight').textContent  = data.weight || 'N/A';
          document.getElementById('vbp').textContent  = data.bloodpressure || 'N/A';
          document.getElementById('vbt').textContent  = data.bloodtype || 'N/A';
          document.getElementById('vsmoking').textContent  = data.smoking || 'N/A';
          document.getElementById('vliquordrinking').textContent  = data.liquor || 'N/A';
          
          
          // document.getElementById('vwmedical_id').textContent  = data.medical_id || 'N/A';

        // Populate other fields as needed
      } else {
        console.error('No data found for the specified ID.');
      }
    })
}
function openUpdateModal(modal, userId) {
  if (modal == null || userId == null) return;
  modal.classList.add('active');
  overlay.classList.add('active');

  // Fetch data using the userId and populate the modal
  fetch('?action=view&id=' + userId)
    .then(response => response.json())
    .then(data => {
      if (data && Object.keys(data).length > 0) {
          document.getElementById('viewId').value = data.id_no || 'N/A';
          document.getElementById('viewlname').value = data.lname || 'N/A';
          document.getElementById('viewfname').value = data.fname || 'N/A';
          document.getElementById('viewmname').value = data.mname || 'N/A';
          document.getElementById('viewbday').value = data.bday || 'N/A';
          document.getElementById('viewgender').value = data.gender || 'N/A';
          document.getElementById('viewreligion').value = data.religion || 'N/A';
          document.getElementById('viewcourse').value = data.course || 'N/A';
          document.getElementById('viewyear').value  = data.year_level || 'N/A';
          document.getElementById('viewsection').value  = data.section || 'N/A';
          document.getElementById('viewcontact_no').value = data.contact_no || 'N/A';
          document.getElementById('viewaddress').value = data.address || 'N/A';
          document.getElementById('viewguardian').value = data.guardian || 'N/A';
          document.getElementById('viewemergency').value = data.emergency_no || 'N/A';
          document.getElementById('viewallergy').value = data.allergy || 'N/A';
          document.getElementById('viewasthma').value = data.asthma || 'N/A';
          document.getElementById('viewdiabetes').value = data.diabetes || 'N/A';
          document.getElementById('viewheartdisease').value = data.heartdisease || 'N/A';
          document.getElementById('viewseizure').value = data.seizure || 'N/A';
          document.getElementById('viewothers').value = data.others || 'N/A';
          document.getElementById('viewmedication').value = data.medication || 'N/A';
          document.getElementById('viewcovidvax').value = data.vaccine || 'N/A';
          document.getElementById('viewVacStat').value = data.vaccine_status || 'N/A';
          document.getElementById('viewdateOfexamination').value = data.dOexamination || 'N/A';
          document.getElementById('viewheight').value = data.height || 'N/A';
          document.getElementById('viewweight').value = data.weight || 'N/A';
          document.getElementById('viewbmi').value = data.bmi || 'N/A';
          document.getElementById('viewbp').value = data.bloodpressure || 'N/A';
          document.getElementById('viewbt').value = data.bloodtype || 'N/A';
          document.getElementById('viewsmoking').value = data.smoking || 'N/A';
          document.getElementById('viewliquordrinking').value = data.liquor || 'N/A';
          document.getElementById('viewpicture').src = data.picture || 'N/A';
          document.getElementById('viewunique_id').value = data.unique_id || 'N/A';
          document.getElementById('viewmedical_id').value = data.medical_id || 'N/A';

        // Populate other fields as needed
      } else {
        console.error('No data found for the specified ID.');
      }
    })
    .catch(error => console.error('Error fetching data:', error));
}
function openUpdateUser(modal, userId) {
  if (modal == null || userId == null) return;
  modal.classList.add('active');
  overlay.classList.add('active');

  // Fetch data using the userId and populate the modal
  fetch('?action=view&id=' + userId)
  .then(response => response.text())  // Change to response.text()
  .then(data => {
    console.log('Server response:', data);  // Log the raw response
    // Try to parse the data as JSON if it's in the correct format
    const parsedData = JSON.parse(data);
    if (parsedData && Object.keys(parsedData).length > 0) {
      document.getElementById('viewuser_id').value = parsedData.user_id || 'N/A';
      document.getElementById('viewprc_id').value = parsedData.prc_id || 'N/A';
      document.getElementById('viewfname').value = parsedData.fname || 'N/A';
      document.getElementById('viewlname').value = parsedData.lname || 'N/A';
          document.getElementById('viewmname').value = parsedData.mname || 'N/A';
          document.getElementById('viewcontact_no').value = parsedData.contact_no || 'N/A';
          
          document.getElementById('viewemail').value = parsedData.email || 'N/A';

        // Populate other fields as needed
      } else {
        console.error('No data found for the specified ID.');
      }
    })
    .catch(error => console.error('Error fetching data:', error));
}
const select = document.querySelector('.select-btnMed');
const menu = document.querySelector('.contentddMed');
const options = document.querySelectorAll('.options li');
const searchBox = document.querySelector('.searchddMed input');
const selected = document.querySelector('.selected');
const caret = document.querySelector('.caret');

// Toggle dropdown menu
select.addEventListener('click', () => {
    caret.classList.toggle('rotate');
    menu.classList.toggle('active');
    searchBox.value = ""; // Clear search box
    filterOptions(""); // Reset filter on dropdown open
});

// Add event listener to each option
options.forEach(option => {
    option.addEventListener('click', () => {
        selected.innerText = option.innerText;
        caret.classList.remove('rotate');
        menu.classList.remove('active');

        options.forEach(option => {
            option.classList.remove('selected-option');
        });
        option.classList.add('selected-option');
    });
});

// Filter options based on search query
searchBox.addEventListener('keyup', function() {
    const search = searchBox.value.toLowerCase();
    filterOptions(search);
});

function filterOptions(search) {
    options.forEach(option => {
        const optionText = option.innerText.toLowerCase();
        if (optionText.indexOf(search) !== -1) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    });
    
}

// Close the dropdown if clicked outside
document.addEventListener('click', function(e) {
    // Check if the click is outside the dropdown
    if (!select.contains(e.target) && !menu.contains(e.target)) {
        caret.classList.remove('rotate');
        menu.classList.remove('active');
    }
});