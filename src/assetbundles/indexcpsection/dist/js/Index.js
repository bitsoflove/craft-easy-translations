/**
 * Translation plugin for Craft CMS
 *
 * Index Field JS
 *
 * @author    bitsoflove
 * @copyright Copyright (c) 2022 bitsoflove
 * @link      https://www.bitsoflove.be/
 * @package   Translation
 * @since     0.0.1
 */

window.addEventListener("DOMContentLoaded", init);

function init() {
    setSiteId();
    setCategory();

    document.querySelector("#site-button").addEventListener("click", () => {
        document.querySelectorAll(".translation-site-ids").forEach(a => {a.addEventListener("click", setSiteId)});
    });

    document.querySelectorAll('[data-key^="categories"], [data-key^="templates"]').forEach(a => {a.addEventListener("click", setCategory)});

    document.querySelector("#import-button").addEventListener("click", handleImport);
    document.querySelector("#save-button").addEventListener("click", handleSave);
    document.querySelector("#save-shortcut-button").addEventListener("click", handleSave);
}

function setSiteId() {
    document.querySelectorAll(".translation-site-id").forEach(input => {input.value = Craft.elementIndex.siteId});
}

function setCategory() {
    const categories = document.querySelectorAll('[data-key^="categories"]');
    const inputs = document.querySelectorAll(".translation-category");
    inputs.forEach(input => {input.value = "site" });

    categories.forEach(c => {
       if (c.classList.contains("sel")) {
           inputs.forEach(input => {input.value = c.getAttribute("data-key").split(":")[1] });
       }
    });
}

function handleImport() {    
    const fileButton = document.querySelector("input[name='translation-import']");
    fileButton.click();

    fileButton.addEventListener("change", () => {
        document.querySelector("#import-form").submit();
    });
}

function handleSave() {
    document.querySelector("#translation-form").submit();
}