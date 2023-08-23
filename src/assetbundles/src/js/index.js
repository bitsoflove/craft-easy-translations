/**
 * @author    Bits of Love
 * @copyright Copyright (c) 2022 bitsoflove
 * @link      https://www.bitsoflove.be/
 * @package   craft-easy-translations
 * @since     1.0.0
 */

window.addEventListener('DOMContentLoaded', init);

function init() {
  setSiteId();
  setCategory();

  document.querySelector('#site-button').addEventListener('click', () => {
    document.querySelectorAll('.translation-site-ids').forEach((a) => {
      a.addEventListener('click', setSiteId);
    });
  });

  document.querySelectorAll('[data-key^="categories"], [data-key^="templates"]').forEach((a) => {
    a.addEventListener('click', setCategory);
  });

  document.querySelector('#import-button').addEventListener('click', handleImport);
  document.querySelector('#save-button').addEventListener('click', handleSave);
  document.querySelector('#save-shortcut-button').addEventListener('click', handleSave);

  document.addEventListener('keydown', handleShortcutSave);
}

function handleShortcutSave(e) {
  if (Garnish.isCtrlKeyPressed(e) && e.code === 'KeyS') {
    e.preventDefault();
    handleSave();
  }
}

function setSiteId() {
  document.querySelectorAll('.translation-site-id').forEach((input) => {
    input.value = Craft.elementIndex.siteId;
  });
}

function setCategory() {
  const categories = document.querySelectorAll('[data-key^="categories"]');
  const inputs = document.querySelectorAll('.translation-category');
  inputs.forEach((input) => {
    console.log(input);
    input.value = 'site';
  });

  categories.forEach((c) => {
    if (c.classList.contains('sel')) {
      inputs.forEach((input) => {
        input.value = c.getAttribute('data-key').split(':')[1];
      });
    }
  });
}

function handleImport() {
  const fileButton = document.querySelector("input[name='translation-import']");
  fileButton.click();

  fileButton.addEventListener('change', () => {
    document.querySelector('#import-form').submit();
  });
}

function handleSave() {
  const form = document.querySelector('#translation-form');
  const submitFormFunction = Object.getPrototypeOf(form).submit;
  submitFormFunction.call(form);
}
