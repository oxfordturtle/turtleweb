import './style.scss'
import setImage from '../../common/setImage.js'
import compsci from './compsci.png'
import govcrest from './govcrest.png'
import hertcrest from './hertcrest.png'
import philfac from './philfac.png'

function init () {
  setImage('.compsci', compsci)
  setImage('.govcrest', govcrest)
  setImage('.hertcrest', hertcrest)
  setImage('.philfac', philfac)
}

document.addEventListener('DOMContentLoaded', init)
