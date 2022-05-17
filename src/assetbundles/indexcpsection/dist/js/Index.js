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

    document.querySelector("#site-button").addEventListener("click", () => {
        document.querySelectorAll(".translation-site-ids").forEach(a => {a.addEventListener("click", setSiteId)});
    });

    document.querySelector('#import-button').addEventListener("click", handleImport);
    document.querySelector('#save-button').addEventListener("click", handleSave);
    document.querySelector('#save-shortcut-button').addEventListener("click", handleSave);
}

function setSiteId() {
    document.querySelectorAll(".translation-site-id").forEach(input => {input.value = Craft.elementIndex.siteId});
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