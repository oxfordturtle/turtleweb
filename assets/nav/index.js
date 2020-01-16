import './style.scss'
import '@fortawesome/fontawesome-free/js/fontawesome.js'
import '@fortawesome/fontawesome-free/js/solid.js'
import './toggle-menu.js'
import './toggle-sub-menu.js'
import turtleLogo from './turtle.png'

Array.from(document.querySelectorAll('.logo > img')).forEach((img) => {
  img.src = turtleLogo
})
