document.getElementById('profileForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  // Show loading state
  const saveBtn = document.querySelector('.save-btn');
  const originalBtnText = saveBtn.textContent;
  saveBtn.disabled = true;
  saveBtn.textContent = 'Saving...';

  fetch('../profile_php/update_profile.php', {
    method: 'POST',
    body: formData
  })
    .then(res => {
      if (!res.ok) {
        throw new Error('Network response was not ok');
      }
      return res.json();
    })
    .then(data => {
      const toast = document.getElementById('toast');
      const toastMsg = document.getElementById('toastMsg');

      // Restore button
      saveBtn.disabled = false;
      saveBtn.textContent = originalBtnText;

      if (data.success) {
        let message = "✔ Profile updated successfully!";
        if (data.image_warning) {
          message += " (Note: " + data.image_warning + ")";
        }
        toastMsg.textContent = message;
        toast.classList.add("show");
        toast.classList.remove("error");

        if (data.updated_phone) {
          document.getElementById('phone').value = data.updated_phone;
        }

        if (data.new_image) {
          const preview = document.getElementById('preview');
          preview.src = data.new_image + '?t=' + new Date().getTime(); // refresh cache
          
          // ✅ Update user-menu image immediately
          const userMenuImg = document.querySelector('.user-menu .user-icon');
          if (userMenuImg) {
            userMenuImg.src = data.new_image + '?t=' + new Date().getTime();
          }
        }

        // ✅ Force refresh of navbar (image & name) after 2 sec
        setTimeout(() => {
          location.reload();
        }, 2000);
      } else {
        toastMsg.textContent = "⚠ " + (data.message || 'Failed to update profile');
        toast.classList.add("error", "show");
      }

      setTimeout(() => toast.classList.remove("show"), 5000);
    })
    .catch(err => {
      console.error('Error:', err);
      const toast = document.getElementById('toast');
      const toastMsg = document.getElementById('toastMsg');
      
      // Restore button
      saveBtn.disabled = false;
      saveBtn.textContent = originalBtnText;
      
      toastMsg.textContent = "⚠ Error: " + (err.message || 'Failed to save. Please try again.');
      toast.classList.add("error", "show");
      setTimeout(() => toast.classList.remove("show"), 5000);
    });
});

document.getElementById('upload').addEventListener('change', function (e) {
  const [file] = e.target.files;
  if (file) document.getElementById('preview').src = URL.createObjectURL(file);
});
