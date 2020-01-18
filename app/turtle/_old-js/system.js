import '../css/system.scss'
import program from './system/program.js'
import machine from './system/machine.js'
import modal from './system/modal.js'
import * as dom from './system/components/dom.js'
import { send } from './system/system/state.js'

// look for the #tse element
const tse = document.getElementById('tse')

if (tse) {
  // add the css classes
  tse.classList.add('tse')
  tse.classList.add('tse-browser')

  // initialise the app
  const programTab = dom.createElement('a', 'tse-browser-tab', 'Program')
  const machineTab = dom.createElement('a', 'tse-browser-tab', 'Machine')
  const programTabPane = program()
  const machineTabPane = machine()
  const tabList = dom.createElement('nav', 'tse-browser-tab-list', [programTab, machineTab])
  const tabPanes = dom.createElement('div', 'tse-browser-tab-panes', [programTabPane, machineTabPane])
  const modalDiv = modal()
  programTab.addEventListener('click', (e) => {
    machineTab.classList.remove('active')
    programTab.classList.add('active')
    machineTabPane.classList.remove('active')
    programTabPane.classList.add('active')
  })
  machineTab.addEventListener('click', (e) => {
    programTab.classList.remove('active')
    machineTab.classList.add('active')
    programTabPane.classList.remove('active')
    machineTabPane.classList.add('active')
  })
  machineTab.classList.add('active')
  machineTabPane.classList.add('active')
  dom.setContent(tse, [tabList, tabPanes])
  document.body.appendChild(modalDiv)

  // maybe setup state variables based on the app's data properties
  if (tse.dataset.language) {
    send('set-language', tse.dataset.language)
  }
  if (tse.dataset.example) {
    send('set-example', tse.dataset.example)
  }
  if (tse.dataset.file) {
    send('load-remote-file', tse.dataset.file)
  }

  // send the page ready signal (which will update the components to reflect the initial state)
  send('ready')
}
