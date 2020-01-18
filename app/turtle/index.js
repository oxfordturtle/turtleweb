import './style.scss'
import { send } from './state/index.js'
import * as dom from './components/dom.js'
import menu from './components/menu/index.js'
import system from './components/system/index.js'

function init () {
  // look for the turtle element
  const turtle = document.getElementById('turtle')

  // set up if it's there
  if (turtle) {
    // load the content
    dom.setContent(turtle, [menu, system])

    // maybe setup state variables based on the app's data properties
    if (turtle.dataset.language) {
      send('set-language', turtle.dataset.language)
    }

    if (turtle.dataset.example) {
      send('set-example', turtle.dataset.example)
    }

    if (turtle.dataset.file) {
      send('load-remote-file', turtle.dataset.file)
    }

    // send the page ready signal (which will update the components to reflect the initial state)
    send('ready')
  }
}

document.addEventListener('DOMContentLoaded', init)
