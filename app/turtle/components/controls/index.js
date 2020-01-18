/*
The system control bar.
*/
import './style.scss'
import * as dom from '../dom.js'
import languages from '../../constants/languages.js'
import { send, on } from '../../state/index.js'

// menu button
const menuButton = dom.createElement({
  type: 'button',
  classes: 'turtle-icon-text',
  content: [dom.createElement({
    type: 'span',
    classes: 'icon',
    content: [dom.createElement({ type: 'i', classes: 'fa fa-bars' })]
  })]
})

// language select menu
const languageSelect = dom.createElement({
  type: 'select',
  classes: 'language-select',
  content: languages.map(language => dom.createElement({
    type: 'option',
    content: language,
    value: language
  }))
})

// machine RUN/PAUSE button
const runButton = dom.createIconWithText({ type: 'button', icon: 'fa fa-play', text: 'RUN' })

// machine HALT button
const haltButton = dom.createIconWithText({ type: 'button', icon: 'fa fa-stop', text: 'HALT' })

// the controls div
export default dom.createElement({
  type: 'div',
  classes: 'turtle-controls',
  content: [
    dom.createElement({ type: 'div', content: [menuButton] }),
    dom.createElement({ type: 'div', content: [languageSelect, runButton, haltButton] })
  ]
})

// setup event listeners on interactive elements
menuButton.addEventListener('click', (e) => {
  e.stopPropagation()
  menuButton.blur()
  send('open-menu')
})

languageSelect.addEventListener('change', (e) => {
  send('set-language', languageSelect.value)
})

runButton.addEventListener('click', (e) => {
  runButton.blur()
  send('machine-run')
})

haltButton.addEventListener('click', (e) => {
  haltButton.blur()
  send('machine-halt')
})

// subscribe to keep in sync with system state
on('language-changed', (language) => {
  languageSelect.value = language
})

on('machine-started', () => {
  // TODO
})

on('machine-stopped', () => {
  // TODO
})

on('machine-played', () => {
  // TODO
})

on('machine-paused', () => {
  // TODO
})
