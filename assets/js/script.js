document.addEventListener("DOMContentLoaded", () => {
    // just open the menu
    document.querySelectorAll(['[data-menu-open]']).forEach((el) => el.addEventListener('click', event => {
        event.preventDefault();
        const menu = document.getElementById(el.getAttribute('data-menu-open'));
        menu.style.display = 'block';
    }));
    // just close the menu
    document.querySelectorAll(['[data-menu-close]']).forEach((el) => el.addEventListener('click', event => {
        event.preventDefault();
        const menu = document.getElementById(el.getAttribute('data-menu-close'));
        menu.style.display = 'none';
    }));
    // switch between open and close
    document.querySelectorAll(['[data-menu-toggle]']).forEach((el) => el.addEventListener('click', event => {
        event.preventDefault();
        const menu = document.getElementById(el.getAttribute('data-menu-toggle'));
        menu.style.display = menu.style.display == 'block'? 'none': 'block';
    }));
});
