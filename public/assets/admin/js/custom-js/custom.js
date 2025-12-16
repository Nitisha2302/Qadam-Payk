document.addEventListener('DOMContentLoaded', function () {

    /*************** ADD SERVICE ***************/
    let selectedFile = null;

    window.previewImage = function () {
        const fileInput = document.getElementById('fileInput1');
        const imagePreview = document.getElementById('imagePreview1');
        const file = fileInput.files[0];
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/svg+xml'];

        imagePreview.innerHTML = '';
        document.querySelector('.error-message')?.remove();

        if (!file) return;

        if (!allowedTypes.includes(file.type)) {
            alert('Only JPEG and PNG images are allowed.');
            fileInput.value = '';
            selectedFile = null;
            return;
        }

        selectedFile = file;

        const reader = new FileReader();
        reader.onload = function(e) {
            const imgContainer = document.createElement('div');
            imgContainer.style.position = 'relative';
            imgContainer.style.margin = '5px';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '80px';
            img.style.height = '80px';
            img.style.border = '1px solid #ddd';
            img.style.borderRadius = '5px';
            img.style.objectFit = 'cover';

            const closeIcon = document.createElement('span');
            closeIcon.innerHTML = '&times;';
            closeIcon.style.position = 'absolute';
            closeIcon.style.top = '-6px';
            closeIcon.style.right = '-5px';
            closeIcon.style.cursor = 'pointer';
            closeIcon.style.color = 'white';
            closeIcon.style.background = 'red';
            closeIcon.style.borderRadius = '50%';
            closeIcon.style.padding = '0px 7px';
            closeIcon.style.fontWeight = 'bold';
            closeIcon.onclick = function() {
                fileInput.value = '';
                selectedFile = null;
                imgContainer.remove();
            };

            imgContainer.appendChild(img);
            imgContainer.appendChild(closeIcon);
            imagePreview.appendChild(imgContainer);
        };
        reader.readAsDataURL(file);
    };

    $('#serviceForm').on('submit', function(e){
        e.preventDefault();
        $('.error-message').remove();

        if (!selectedFile) {
            $('#imagePreview1').after('<div class="text-danger error-message">Image is required.</div>');
            return;
        }

        let formData = new FormData(this);
        $.ajax({
            url: serviceStoreData,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response){
                window.location.href = response.redirect + '?successMessage=' + encodeURIComponent(response.message);
            },
            error: function(xhr){
                if(xhr.status === 422){
                    let errors = xhr.responseJSON.errors;
                    if(errors.service_name){
                        $('#service_name').after('<div class="text-danger error-message">' + errors.service_name[0] + '</div>');
                    }
                    if(errors.image){
                        $('#imagePreview1').after('<div class="text-danger error-message">' + errors.image[0] + '</div>');
                    }
                } else {
                    alert('Something went wrong.');
                }
            }
        });
    });


    /*************** EDIT SERVICE ***************/
    let selectedEditFile = null;

    window.previewEditImage = function() {
        const fileInput = document.getElementById('fileInputEdit');
        const preview = document.getElementById('imagePreviewEdit');
        const file = fileInput.files[0];
        const allowedTypes = ['image/jpeg','image/jpg','image/png', 'image/svg+xml'];

        preview.innerHTML = '';
        document.querySelector('.error-message')?.remove();

        if (!file) return;

        if (!allowedTypes.includes(file.type)) {
            alert('Only JPEG and PNG images are allowed.');
            fileInput.value = '';
            selectedEditFile = null;
            return;
        }

        selectedEditFile = file;

        const reader = new FileReader();
        reader.onload = function(e) {
            const container = document.createElement('div');
            container.style.position = 'relative';
            container.style.margin = '5px';

            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '80px';
            img.style.height = '80px';
            img.style.border = '1px solid #ddd';
            img.style.borderRadius = '5px';
            img.style.objectFit = 'cover';

            const closeIcon = document.createElement('span');
            closeIcon.innerHTML = '&times;';
            closeIcon.style.position = 'absolute';
            closeIcon.style.top = '-6px';
            closeIcon.style.right = '-5px';
            closeIcon.style.cursor = 'pointer';
            closeIcon.style.color = 'white';
            closeIcon.style.background = 'red';
            closeIcon.style.borderRadius = '50%';
            closeIcon.style.padding = '0px 7px';
            closeIcon.style.fontWeight = 'bold';
            closeIcon.onclick = function() {
                fileInput.value = '';
                selectedEditFile = null;
                container.remove();
            };

            container.appendChild(img);
            container.appendChild(closeIcon);
            preview.appendChild(container);
        };
        reader.readAsDataURL(file);
    };

    $('#serviceEditForm').on('submit', function(e){
        e.preventDefault();
        $('.error-message').remove();

        let formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(response){
                window.location.href = response.redirect + '?successMessage=' + encodeURIComponent(response.message);
            },
            error: function(xhr){
                if(xhr.status === 422){
                    let errors = xhr.responseJSON.errors;
                    if(errors.service_name){
                        $('#service_name').after('<div class="text-danger error-message">' + errors.service_name[0] + '</div>');
                    }
                    if(errors.image){
                        $('#imagePreviewEdit').after('<div class="text-danger error-message">' + errors.image[0] + '</div>');
                    }
                } else {
                    alert('Something went wrong.');
                }
            }
        });
    });




   


    //  *************************store services data end*********************************************************************** 


    //  *************************edit neutrition data start*********************************************************************** 

    window.allSelectedFiles = [];

    window.previewImagesEditNeutrition = function () {
        const fileInput = document.getElementById('fileInput1');
        const imagePreview = document.getElementById('imagePreview1');
        const files = Array.from(fileInput.files);
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

        for (let file of files) {
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPEG and PNG images are allowed.');
                fileInput.value = '';
                return;
            }

            // Add new valid file to global array
            window.allSelectedFiles.push(file);

            const reader = new FileReader();
            reader.onload = function (e) {
                const wrapper = document.createElement('div');
                wrapper.classList.add('foto-grid-image', 'me-2', 'mb-2');
                wrapper.style.position = 'relative';

                wrapper.innerHTML = `
                    <img src="${e.target.result}" alt="preview" width="80" height="80" style="object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                    <span class="remove-image-btn" style="position:absolute;top:-5px;right:-5px;background:red;color:#fff;border-radius:50%;padding:0px 6px;font-weight:bold;cursor:pointer;">×</span>
                `;

                // Add remove logic
                wrapper.querySelector('.remove-image-btn').onclick = function () {
                    const indexToRemove = Array.from(imagePreview.children).indexOf(wrapper);
                    window.allSelectedFiles.splice(indexToRemove, 1);
                    wrapper.remove();
                    updateFileInput();
                };

                imagePreview.appendChild(wrapper);
                updateFileInput(); // update hidden input on every new image
            };

            reader.readAsDataURL(file);
        }

        // Reset file input to allow selecting the same image again
        fileInput.value = '';
    };

    function updateFileInput() {
        // Create a new FileList from the current global files
        const dataTransfer = new DataTransfer();
        window.allSelectedFiles.forEach(file => dataTransfer.items.add(file));
        document.getElementById('fileInput1').files = dataTransfer.files;
    }


    // Helper to convert a single File into a FileList
    function createFileList(file) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        return dataTransfer.files;
    }


    window.removeExistingImage = function (element) {
        const container = element.closest('.db-image');
        container.remove();
    };

    window.removedImageFiles = [];

    window.removeExistingImage = function (element) {
        const container = element.closest('.db-image');
        const filename = container.getAttribute('data-filename');

        if (filename && !window.removedImageFiles.includes(filename)) {
            window.removedImageFiles.push(filename);
            document.getElementById('removedImagesInput').value = window.removedImageFiles.join(',');
        }

        container.remove();
    };

    $('#editNeutritionAiData').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
            $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: aiNeutritionEditData,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
      
             success: function (response) {
                if (response.redirect) {
                    // 🔄 Do the redirect
                    window.location.href = response.redirect;
                } else {
                    $('#successMessage')
                        .removeClass('d-none')
                        .text(response.message);
                }
            },
            error: function (xhr) {
                $('.error-message').remove(); // clear old errors
                if (xhr.status === 422) {
                 let errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        let inputField = $('[name="' + key + '"]');
                        let errorDiv = $('<div class="text-danger error-message">' + value[0] + '</div>');
                        inputField.after(errorDiv);

                        // Remove error after 3 seconds
                        setTimeout(function() {
                            errorDiv.fadeOut(300, function() { $(this).remove(); });
                        }, 3000);
                    });
                    
                } else {
                    alert("Er ging iets mis. Probeer het opnieuw.");
                }
            }
            
        });
    });

    


    //  *************************edit neutrition data end*********************************************************************** 


    // --- Your image preview code end ---

    // Hide the success message after 2 seconds
    setTimeout(function () {

        var successMessage = document.getElementById('success-message');
        if (successMessage) {
            successMessage.style.transition = 'opacity 0.5s';

            successMessage.style.opacity = 0;
            setTimeout(function () {
                successMessage.style.display = 'none';
            }, 200); // Wait for the fade-out to finish before setting display to none
        }
    }, 2000); // 2000 milliseconds = 2 seconds
    
    // Hide the session error message after 2 seconds
    setTimeout(function () {
        var errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            errorMessage.style.transition = 'opacity 0.5s';
            errorMessage.style.opacity = 0;
            setTimeout(function () {
                errorMessage.style.display = 'none';
            }, 500); // Adjusted to match fade-out duration
        }
    }, 2000);
    
    // Function to hide all error messages after a specified delay
    function hideErrorsAfterDelay() {
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach((errorElement) => {
            // Find the parent div with class 'icon-box' and remove the 'is-error' class from it
            const iconBox = errorElement.previousElementSibling; // This gets the div with 'icon-box email-icon'
            setTimeout(() => {
                errorElement.style.transition = 'opacity 0.5s'; // Optional transition effect
                errorElement.style.opacity = 0; // Fade out
                iconBox.classList.remove('is-error');
                setTimeout(() => {
                    errorElement.style.display = 'none'; // Remove from the display
                }, 500); // Wait for the fade-out to complete
            }, 2000); // Delay of 1 second
        });
    }
    
    // Hide error messages
    hideErrorsAfterDelay();

    // Get all toggle buttons
    let togglePasswordBtns = document.querySelectorAll('.toggle-password');

    togglePasswordBtns.forEach(function (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function (e) {
            // Get the input field associated with this toggle using the `toggle` attribute
            let inputSelector = togglePasswordBtn.getAttribute('toggle');
            let passwordField = document.querySelector(inputSelector);

            if (passwordField) {
                if (passwordField.type === "password") {
                    passwordField.type = "text";
                    togglePasswordBtn.classList.remove('fa-eye-slash');
                    togglePasswordBtn.classList.add('fa-eye');
                } else {
                    passwordField.type = "password";
                    togglePasswordBtn.classList.remove('fa-eye');
                    togglePasswordBtn.classList.add('fa-eye-slash');
                }
            }
        });
    });

});



$(document).ready(function () {
    function checkScreenSize() {
        if ($(window).width() < 1200) {
            $(".sidebar-nav, .navbar-box, .main-box-content").addClass("tabsmode");
        } else {
            $(".sidebar-nav, .navbar-box, .main-box-content").removeClass("tabsmode");
        }
    }

    
    checkScreenSize();

    $(window).resize(function () {
        checkScreenSize();
    });


    $("#nav_slidebar").click(function () {
        if ($(".sidebar-nav").hasClass("tabsmode")) { 
        
            $(".sidebar-nav, .navbar-box, .main-box-content").removeClass("tabsmode");
            $(".sidebar-nav, .navbar-box, .main-box-content").addClass("overlap-nav");
            $(".overlay-box").addClass("active");
        } else {
            $(".sidebar-nav, .navbar-box, .main-box-content").removeClass("overlap-nav");
            $(".sidebar-nav, .navbar-box, .main-box-content").addClass("tabsmode");
            $(".overlay-box").removeClass("active");
        }

    });
    $(document).on('click', '.overlay-box.active', function () {
        // Remove the active class from overlay-box
        $(".overlay-box").removeClass("active");
        // Remove the overlap-nav class from sidebar-nav
        $(".sidebar-nav").removeClass("overlap-nav");
        $(".sidebar-nav").addClass("tabsmode");
    });


    $("#nav_slidebar").click(function () {
        if ($(window).width() > 1200) { 
            $(".sidebar-nav, .navbar-box, .main-box-content").toggleClass("active");
            $(".sidebar-nav, .navbar-box, .main-box-content").removeClass("tabsmode");
        }
    });


});


// stpre feeddback data 

 $('#feedbackForm').on('submit', function (e) {
        e.preventDefault();
        $('.error-message').remove();

        let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        
        $.ajax({
            url: feedbackStoreData,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                $('#feedbackForm')[0].reset();
                window.allSelectedFiles = [];
                document.getElementById('imagePreview1').innerHTML = '';
                //   $('#successMessage').removeClass('d-none').text('AI data succesvol opgeslagen.').fadeIn().delay(3000).fadeOut();
                //    window.location.href = aiRedirectUrl + '?success=1';
                    window.location.href = response.redirect + '?successMessage=' + encodeURIComponent(response.message);

            },
            error: function (xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                for (let field in errors) {
                    let inputElem = $('[name="' + field + '"]');
                    if (inputElem.length) {
                        inputElem.after('<div class="text-danger error-message">' + errors[field][0] + '</div>');
                    }
                }
            } else {
                alert('Er is iets misgegaan. Probeer het later opnieuw.');
            }
        }

        });
    });















  
  





