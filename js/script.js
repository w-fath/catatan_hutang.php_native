var addModal = document.getElementById("addDebtModal");
var addBtn = document.getElementById("addDebtBtn");
var addClose = addModal.getElementsByClassName("close")[0];
var editModal = document.getElementById("editDebtModal");
var editClose = editModal.getElementsByClassName("close")[0];
var debtForm = document.getElementById("debtForm");
var editForm = document.getElementById("editForm");

// Show Add Modal
addBtn.onclick = function() {
    addModal.style.display = "block";
};

// Close Add Modal
addClose.onclick = function() {
    addModal.style.display = "none";
};

// Close Edit Modal
editClose.onclick = function() {
    editModal.style.display = "none";
};

// Close Modals when clicking outside
window.onclick = function(event) {
    if (event.target == addModal) {
        addModal.style.display = "none";
    } else if (event.target == editModal) {
        editModal.style.display = "none";
    }
};

// Handle Edit Form Submission
editForm.addEventListener("submit", function(event) {
    event.preventDefault();
    var formData = new FormData(editForm);
    fetch('edit_debt.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert(data.message);
            window.location.reload(); // Reload the page to reflect the update
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengirim data. Silakan coba lagi.');
    });
});

// Populate Edit Modal with data
var editButtons = document.querySelectorAll(".edit-btn");
editButtons.forEach(function(btn) {
    btn.addEventListener("click", function() {
        var id = btn.getAttribute("data-id");
        var status = btn.closest("tr").querySelector("td:nth-child(5)").textContent.trim(); // Assuming status is in the 5th column

        document.getElementById("edit-id").value = id;
        document.getElementById("edit-status").value = status;

        editModal.style.display = "block";
    });
});
