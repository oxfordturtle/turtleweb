import './style.scss'
import * as dom from '../dom.js'
import { send, on } from '../../state/index.js'

// create the clickable menu items
const fileLink = dom.createIconWithText({ type: 'a', icon: 'fa fa-folder-open', text: 'Files' })
const editLink = dom.createIconWithText({ type: 'a', icon: 'fa fa-terminal', text: 'Code Editor' })
const usageLink = dom.createIconWithText({ type: 'a', icon: 'fa fa-chart-bar', text: 'Command Usage' })
const lexemesLink = dom.createIconWithText({ type: 'a', icon: 'fa fa-list', text: 'Program Lexemes' })
const pcodeLink = dom.createIconWithText({ type: 'a', icon: 'fa fa-code', text: 'Compiled Code' })
const canvasLink = dom.createIconWithText({ type: 'a', icon: 'fa fa-image', text: 'Canvas and Console' })
const outputLink = dom.createIconWithText({ type: 'a', icon: 'fa fa-align-left', text: 'Textual Output' })
const memoryLink = dom.createIconWithText({ type: 'a', icon: 'fa fa-memory', text: 'Machine Memory' })
const optionsLink = dom.createIconWithText({ type: 'a', icon: 'fa fa-cogs', text: 'Runtime Options' })

// create the menu
const menu = dom.createElement({
  type: 'nav',
  classes: 'turtle-menu',
  content: [
    dom.createElement({ type: 'h2', content: 'Program' }),
    fileLink,
    editLink,
    usageLink,
    lexemesLink,
    pcodeLink,
    dom.createElement({ type: 'h2', content: 'Machine' }),
    canvasLink,
    outputLink,
    memoryLink,
    optionsLink
  ]
})

// export the menu
export default menu

// add event listeners to send state signals
fileLink.addEventListener('click', (e) => {
  send('close-menu')
  send('show-component', 'file')
})

editLink.addEventListener('click', (e) => {
  send('close-menu')
  send('show-component', 'edit')
})

usageLink.addEventListener('click', (e) => {
  send('close-menu')
  send('show-component', 'usage')
})

lexemesLink.addEventListener('click', (e) => {
  send('close-menu')
  send('show-component', 'lexemes')
})

pcodeLink.addEventListener('click', (e) => {
  send('close-menu')
  send('show-component', 'pcode')
})

canvasLink.addEventListener('click', (e) => {
  send('close-menu')
  send('show-component', 'canvas')
})

outputLink.addEventListener('click', (e) => {
  send('close-menu')
  send('show-component', 'output')
})

memoryLink.addEventListener('click', (e) => {
  send('close-menu')
  send('show-component', 'memory')
})

optionsLink.addEventListener('click', (e) => {
  send('close-menu')
  send('show-component', 'options')
})

// register to update things on state change
on('menu-opened', (e) => {
  menu.classList.add('active')
})

on('menu-closed', (e) => {
  menu.classList.remove('active')
})
