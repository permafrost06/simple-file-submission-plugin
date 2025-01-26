let selectedFileNames = [];

function handleResponse(response) {
    if (response.success) {
        document.querySelector("#custom_file_upload input[type='submit']").disabled = true;
        document.querySelector("#custom_file_upload input[type='submit']").value = "Submitted";
        document.querySelector("#custom_file_upload input[type='submit']").classList.add("success");
        document.querySelector("#name_input").disabled = true;
        document.querySelector("#email_input").disabled = true;
        document.querySelector("#fileInput").disabled = true;
    }

    if (response.error) {
        if (response.error.nameError) {
            document.querySelector("#name_input").style.border = "2px solid red";
            document.querySelector("#name_error").innerText = response.error.nameError;
        }
        
        if (response.error.emailError) {
            document.querySelector("#email_input").style.border = "2px solid red";
            document.querySelector("#email_error").innerText = response.error.emailError;
        }

        if (response.error.fileError) {
            document.querySelector(".wrapper-file-input").style.border = "2px solid red";
            document.querySelector(".wrapper-file-input").style.borderBottom = "none";
            document.querySelector(".wrapper-file-section").style.border = "2px solid red";
            document.querySelector(".wrapper-file-section").style.borderTop = "none";
            document.querySelector("#file_error").innerText = response.error.fileError;
        }
    }
}

document.querySelector("#upload_form").onsubmit = function(e) {
    e.preventDefault();

    if (!validateForm()) {
        return;
    }

    let file1 = document.querySelector("#fileInput").files[0];
    let file2 = document.querySelector("#fileInput").files[1];
    let file3 = document.querySelector("#fileInput").files[2];

    let formdata = new FormData();
    formdata.append("name", document.querySelector("#name_input").value);
    formdata.append("email", document.querySelector("#email_input").value);
    formdata.append(
        "cf-turnstile-response",
        document.querySelector("input[name='cf-turnstile-response']").value
    );
    formdata.append("file1", file1);
    if (file2) {
        formdata.append("file2", file2);
    }
    if (file3) {
        formdata.append("file3", file3);
    }

    let http = new XMLHttpRequest();
    http.upload.addEventListener("progress", function(event) {
        let percent = (event.loaded / event.total) * 100;
        document.querySelector(".progress-bar").style.width = Math.round(percent) + "%";
    });

    http.addEventListener("load", function() {
        if (this.readyState == 4 && this.status == 200) {
            handleResponse(JSON.parse(this.responseText));
        }
    });

    http.open("post", "/wp-json/file-upload/v1/file-submission");
    http.send(formdata);
}

document.querySelector("#name_input").addEventListener("input", () => {
    document.querySelector("#name_input").style.border = "1px solid black";
    document.querySelector("#name_error").innerText = "";
});

document.querySelector("#email_input").addEventListener("input", () => {
    document.querySelector("#email_input").style.border = "1px solid black";
    document.querySelector("#email_error").innerText = "";
});

document.querySelector("#input_box_div").addEventListener("click", () => {
    document.querySelector("#fileInput").click();
});

document.querySelector("#fileInput").addEventListener("change", (event) => {
    document.querySelector(".wrapper-file-input").style.border = "none";
    document.querySelector(".wrapper-file-section").style.border = "none";
    document.querySelector("#file_error").innerText = "";

    selectedFileNames = [...event.target.files];

    renderSelectedFiles();
});

function renderSelectedFiles() {
    let html = "";

    selectedFileNames.forEach((f) => {
        html += `<li class="item">
                <span class="name">
                  ${f.name} (${formatFileSize(f.size)})
                </span>
              </li>`;
    });

    document.querySelector(".file-list > span").innerHTML = html;

    if (selectedFileNames.length) {
        document.querySelector(".file-list").style.removeProperty("height");
        document.querySelector(".file-list").style.maxHeight = "220px";
        document.querySelector(".selected-files").style.display = "block";
    } else {
        document.querySelector(".file-list").style.removeProperty("max-height");
        document.querySelector(".file-list").style.height = "auto";
        document.querySelector(".selected-files").style.display = "none";
    }
}

function checkName() {
    const name = document.querySelector("#name_input").value;

    if (name === "") {
        return "Name cannot be empty";
    }

    const nameWords = name.split(" ");

    if (nameWords.length < 2) {
        return "Full name must be provided (both firstname and lastname)";
    }

    if (nameWords.length === 2 && nameWords[1] === "") {
        return "Full name must be provided (both firstname and lastname)";
    }

    return "valid";
}

function checkEmail() {
    const email = document.querySelector("#email_input").value;

    if (email === "") {
        return "Email cannot be empty";
    }

    const emailParts = email.split("@");
    if (emailParts.length < 2) {
        return "Invalid email";
    }

    const domainParts = emailParts[1].split(".");
    if (domainParts.length < 2) {
        return "Invalid email";
    }

    if (domainParts[domainParts.length - 1] === "") {
        return "Invalid email";
    }

    return "valid";
}

function checkFiles() {
    const files = document.querySelector("#fileInput").files;

    if (files.length === 0) {
        return "No files provided";
    }

    if (files.length > 3) {
        return "Maximum 3 files can be submitted";
    }

    const validExtensions = [...files].every(file => {
        const fname = file.name.split(".");
        if (fname[fname.length - 1] === "md" || fname[fname.length - 1] === "docx") {
            return true;
        }
        return false;
    });

    if (!validExtensions) {
        return "One or more files provided have invalid file extensions. Allowed extensions are: .md and .docx";
    }

    return "valid";
}

function validateForm() {
    let valid = true;

    if (checkName() !== "valid") {
        document.querySelector("#name_input").style.border = "2px solid red";
        document.querySelector("#name_error").innerText = checkName();
        valid = false;
    }
    if (checkEmail() !== "valid") {
        document.querySelector("#email_input").style.border = "2px solid red";
        document.querySelector("#email_error").innerText = checkEmail();
        valid = false;
    }
    if (checkFiles() !== "valid") {
        document.querySelector(".wrapper-file-input").style.border = "2px solid red";
        document.querySelector(".wrapper-file-input").style.borderBottom = "none";
        document.querySelector(".wrapper-file-section").style.border = "2px solid red";
        document.querySelector(".wrapper-file-section").style.borderTop = "none";
        document.querySelector("#file_error").innerText = checkFiles();
        valid = false;
    }

    return valid;
}

function formatFileSize(size) {
    const units = ["B", "KB", "MB", "GB"];
    let index = 0;

    while (size >= 1024 && index < units.length - 1) {
        size /= 1024;
        index++;
    }

    return `${size.toFixed(2)} ${units[index]}`;
}

const dropArea = document.getElementById("input_box_div");

dropArea.addEventListener("dragover", (event) => {
    event.stopPropagation();
    event.preventDefault();

    event.dataTransfer.dropEffect = "copy";

    document.querySelector(".file-upload-text").innerText = "Drop to upload your files";
});

dropArea.addEventListener("dragleave", (event) => {
    event.stopPropagation();
    event.preventDefault();

    event.dataTransfer.dropEffect = "none";

    document.querySelector(".file-upload-text").innerText = "Choose file(s) to upload";
});

dropArea.addEventListener("drop", (event) => {
    event.stopPropagation();
    event.preventDefault();

    document.querySelector("#fileInput").files = event.dataTransfer.files

    selectedFileNames = [...event.dataTransfer.files];

    document.querySelector(".file-upload-text").innerText = "Choose file(s) to upload";

    renderSelectedFiles();
});

function turnstileCallback(token) {
    if (token) {
        document.querySelector("#custom_file_upload input[type='submit']").disabled = false;
    } else {
        document.querySelector("#custom_file_upload input[type='submit']").disabled = true;
    }
}

