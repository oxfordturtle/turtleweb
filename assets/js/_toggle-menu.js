/*
 * toggle the main site menu (for small screens)
 */
const toggleMenu = document.querySelector('[data-action="toggle-menu"]')
const menu = document.querySelector('.nav-site')

if (toggleMenu && menu) {
  toggleMenu.addEventListener('click', (e) => {
    e.preventDefault()
    menu.classList.toggle('active')
  })
}
