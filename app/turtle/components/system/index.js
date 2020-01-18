import './style.scss'
import * as dom from '../dom.js'
import controls from '../controls/index.js'
import main from '../main/index.js'
import { send } from '../../state/index.js'

const system = dom.createElement({
  type: 'div',
  classes: 'turtle-system',
  content: [controls, main]
})

export default system

system.addEventListener('click', (e) => {
  send('close-menu')
})
