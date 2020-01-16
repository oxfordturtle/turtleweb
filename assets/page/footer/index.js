import './style.scss'
import compsci from './compsci.png'
import govcrest from './govcrest.png'
import hertcrest from './hertcrest.png'
import philfac from './philfac.png'

Array.from(document.querySelectorAll('.compsci')).forEach(img => {
  img.src = compsci
})

Array.from(document.querySelectorAll('.govcrest')).forEach(img => {
  img.src = govcrest
})

Array.from(document.querySelectorAll('.hertrest')).forEach(img => {
  img.src = hertcrest
})

Array.from(document.querySelectorAll('.philfac')).forEach(img => {
  img.src = philfac
})
