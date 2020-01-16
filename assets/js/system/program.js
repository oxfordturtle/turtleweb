/*
Setup the system page (browser).
*/
import * as dom from './components/dom.js'
import code from './components/program/code.js'
import * as pcode from './components/program/pcode.js'
import * as file from './components/program/file.js'
import usage from './components/program/usage.js'
import lexemes from './components/program/lexemes.js'
import * as controls from './components/controls.js'
import { on } from './system/state.js'

// setup the system page
export default (tse) => {
  // create the program div
  const content = [
    controls.program,
    dom.createTabs([
      { label: 'File', active: false, content: [file.currentFile, file.newFile, file.openFile, file.openExample] },
      { label: 'Code', active: true, content: [code] },
      { label: 'Usage', active: false, content: [usage] },
      { label: 'Lexemes', active: false, content: [lexemes] },
      { label: 'PCode', active: false, content: [pcode.options, pcode.list] }
    ])
  ]
  const programDiv = dom.createElement('div', 'tse-browser-tab-pane', content)
  programDiv.classList.add('tse-program')

  // register to switch tabs when called for
  on('file-changed', () => { dom.showTab('Code') })

  // return the program div
  return programDiv
}
