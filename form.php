<form id="upload_form">
    <div id="custom_file_upload">
        <div class="container">
            <div class="file-input-box">
                <div class="input-holder">
                    <label for="name_input">Name:</label>
                    <input id="name_input" name="name" placeholder="First Last" type="text">
                </div>
                <div class="error" id="name_error"></div>
                <div class="input-holder">
                    <label for="email_input">Email:</label>
                    <input id="email_input" name="email" placeholder="email@domain.tld" type="text">
                </div>
                <div class="error" id="email_error"></div>
                <div class="wrapper-file-input">
                    <div class="input-box" id="input_box_div">
                        <h4>
                            <i class="fa-solid fa-upload"></i>
                            <span class="file-upload-text">Choose file(s) to upload</span>
                        </h4>
                        <input id="fileInput" type="file" accept=".md,.docx" hidden multiple />
                    </div>
                    <small>Files Supported: MD, DOCX</small>
                </div>
                <div class="wrapper-file-section">
                    <div class="selected-files" style="display: none;">
                        <h5>Selected Files</h5>
                        <ul class="file-list" style="height:auto">
                            <span class="selected-files">
                            </span>
                        </ul>
                    </div>
                </div>
                <div class="error file" id="file_error"></div>
                <div
                    class="cf-turnstile"
                    data-sitekey="1x00000000000000000000AA"
                    data-size="flexible"
                    data-theme="light"
                    data-callback="turnstileCallback"
                    >
                </div>
                <div class="error" id="captcha_error"></div>
                <div class="progress-bar"></div>
                <input type="submit" name="submit" disabled value="Submit">
            </div>
        </div>
    </div>
</form>
<style>
#custom_file_upload {
    color: #1b2631;
    margin-block: 10px;
}

@media (max-width: 399px) {
    #custom_file_upload .cf-turnstile {
        margin-left: -3.5vw;
    }
}

#custom_file_upload .input-holder {
    display: flex;
}

#custom_file_upload .input-holder label {
    width: min(17vw, 68px);
    display: inline-block;
}

#custom_file_upload .input-holder input {
    min-width: 100px;
    flex-grow: 1;
    font-size: inherit;
    font-family: inherit;
    padding-inline: 5px;
    border: 1px solid black;
}

#custom_file_upload .error {
    font-size: 15px;
    margin-left: min(17vw, 68px);
    color: red;
    margin-bottom: 10px;
}

#custom_file_upload .error.file {
    margin-left: 0;
}

#custom_file_upload .progress-bar {
    background-color: #064273;
    height: 3px;
    width: 0%;
    transition: width 0.1s ease-out;
    margin-bottom: 20px;
}

#custom_file_upload input[type="submit"] {
    color: #fff;
    background-color: #064273;
    border: none;
    width: fit-content;
    padding: 10px 30px;
    margin-inline: auto;
    cursor: pointer;
    font-family: var(--wp--preset--font-family--manrope);
    font-size: var(--wp--preset--font-size--large);
}

#custom_file_upload input[type="submit"]:disabled {
    background-color: #8cd9ff;
    color: #f5f5f5;
    cursor: not-allowed;
}

#custom_file_upload input[type="submit"]:disabled.success {
    background-color: green;
	background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="white" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209L241 337c-9.4 9.4-24.6 9.4-33.9 0l-64-64c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l47 47L335 175c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9z"/></svg>');
	background-repeat: no-repeat;
	background-size: 30px;
	background-position: 15% 50%;
	padding-left: 60px;
}

#custom_file_upload h2 {
    color: white;
}

#custom_file_upload .file-input-box {
    display: flex;
    justify-content: center;
    flex-direction: column;
    border-radius: 5px;
    box-shadow: 0 5px 10px 0 rgba(0, 0, 0, 0.2);
    max-width: 600px;
    height: auto;
    background-color: #fff;
    padding: 20px;
}

#custom_file_upload .file-input-box .input-box {
    padding: 20px;
    display: grid;
    place-items: center;
    border: 2px dashed #cacaca;
    border-radius: 5px;
    margin-bottom: 5px;
    cursor: pointer;
}

#custom_file_upload .file-input-box .input-box h4 {
    margin: 0;
}

#custom_file_upload .file-input-box small {
    font-size: 12px;
    color: #a3a3a3;
}

#custom_file_upload .file-input-box .wrapper-file-section .selected-files h5 {
    margin-bottom: 10px;
}

#custom_file_upload .file-input-box .wrapper-file-section .selected-files .file-list {
    overflow-y: auto;
    list-style-type: none;
    padding: 0 0 10px 0;
    margin: 0;
    transition: 0.3s all ease-in-out;
}

#custom_file_upload .file-input-box .wrapper-file-section .selected-files .file-list .item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #cacaca;
    border-radius: 5px;
    padding: 10px;
    font-size: 14px;
}

#custom_file_upload .file-input-box .wrapper-file-section .selected-files .file-list .item:not(:last-child) {
    margin-bottom: 5px;
}

#custom_file_upload .file-input-box .wrapper-file-section .selected-files .file-list .item .remove {
    display: grid;
    place-items: center;
    color: #c0392b;
    cursor: pointer;
    transition: 0.3s all ease-in-out;
}

#custom_file_upload .file-input-box .wrapper-file-section .selected-files .file-list .item .remove:hover {
    color: #e74c3c;
}
</style>

