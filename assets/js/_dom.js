// absolute element getters
export const get = function (selector, context) {
  const base = context || document
  return base.querySelector(selector)
}

export const all = function (selector, context) {
  const base = context || document
  return base.querySelectorAll(selector)
}

export const id = function (id, context) {
  const base = context || document
  return base.getElementById(id)
}

export const name = function (name, context) {
  const base = context || document
  return base.getElementsByName(name)
}

export const tag = function (tagName, context) {
  const base = context || document
  return base.getElementsByTagName(tagName)
}

// relative element getters
export const parent = function (element) {
  return element.parentElement
}

export const children = function (element) {
  return element.children
}

export const siblings = function (node) {
  return children(parent(node))
}

// array-like functions
export const each = function (collection, callback) {
  return Array.from(collection).forEach(callback)
}

export const filter = function (collection, callback) {
  return Array.from(collection).filter(callback)
}

export const find = function (collection, callback) {
  return Array.from(collection).find(callback)
}

export const reduce = function (collection, callback, initialValue) {
  return Array.from(collection).reduce(callback, initialValue)
}

// convert all values to/from strings
export const zeroPadding = function (string, length) {
  if (string.length < length) {
    return zeroPadding('0' + string, length)
  } else {
    return string
  }
}

export const stringify = function (value, radix, length) {
  switch (typeof value) {
    case 'function':
      return value.toString()
    case 'number':
      return zeroPadding(value.toString(radix).toUpperCase(), length)
    case 'string':
      return value
    case 'undefined':
      return 'undefined'
    default:
      return JSON.stringify(value)
  }
}

export const parse = function (value) {
  try {
    return JSON.parse(value)
  } catch (ignore) {
    return value
  }
}

// getters and setters for element properties
export const data = function (element, property, value) {
  if (value === undefined) {
    return parse(element.getAttribute('data-' + property))
  }
  element.setAttribute('data-' + property, stringify(value))
}

export const value = function (element, value) {
  const property = (element.type === 'checkbox') ? 'checked' : 'value'
  if (value === undefined) {
    return parse(element[property])
  }
  element[property] = (element.type === 'checkbox') ? value : stringify(value)
}

export const has = function (element, className) {
  return element.classList.contains(className)
}

export const add = function (element, className, unique) {
  if (unique) {
    each(siblings(element), function (element) {
      element.classList.remove(className)
    })
  }
  element.classList.add(className)
}

export const remove = function (element, className) {
  element.classList.remove(className)
}

export const toggle = function (element, className) {
  element.classList.toggle(className)
}

export const css = function (element, property, value) {
  if (value === undefined) {
    return element.style[property]
  }
  element.style[property] = value
}

// session storage getter and setter
export const session = function (item, value) {
  if (value === undefined) {
    return parse(window.sessionStorage.getItem(item))
  }
  window.sessionStorage.setItem(item, stringify(value))
}

// creating and editing elements
export const html = function (element, html) {
  if (html === undefined) {
    return element.innerHTML
  }
  element.innerHTML = html
}

export const append = function (element, child) {
  if (typeof child === 'string') {
    element.innerHTML += child
  } else {
    element.appendChild(child)
  }
}

export const cut = function (element) {
  parent(element).removeChild(element)
}

export const empty = function (element) {
  while (element.firstChild) {
    element.removeChild(element.firstChild)
  }
}

export const fragment = function (children) {
  const frag = document.createDocumentFragment()
  if (children) {
    each(children, function (child) {
      frag.appendChild(child)
    })
  }
  return frag
}

export const element = function (type, inner) {
  const el = document.createElement(type)
  if (inner !== undefined) {
    if (typeof inner === 'object') {
      el.appendChild(inner)
    } else {
      el.innerHTML = inner
    }
  }
  return el
}

// events
export const on = function (element, eventType, callback) {
  element.addEventListener(eventType, callback)
}

export const off = function (element, eventType, callback) {
  element.removeEventListener(eventType, callback)
}

export const one = function (element, eventType, callback) {
  on(element, eventType, function (event) {
    off(element, eventType, callback)
    return callback(event)
  })
}

export const trigger = function (element, eventType) {
  const event = document.createEvent('HTMLEvents')
  event.initEvent(eventType, true, false)
  element.dispatchEvent(event)
}

// miscellaneous
const combineInputs = function (sofar, input) {
  return sofar + '&' + input.name + '=' + value(input)
}

export const serialize = function (form) {
  var inputs = all('input,select,textarea', form)
  return reduce(inputs, combineInputs, '').slice(1)
}

export const today = function () {
  return new Date()
}

export const now = function () {
  return Date.now()
}

export const ajax = function (url, settings) {
  const xhr = new window.XMLHttpRequest()
  // insert defaults
  settings.method = settings.method || 'GET'
  settings.data = settings.data || null
  settings.contentType = settings.contentType ||
    'application/x-www-form-urlencoded charset=UTF-8'
  settings.responseType = settings.responseType || 'text'
  settings.args = settings.args || []
  settings.args.unshift(xhr)
  // setup xhr
  xhr.responseType = settings.responseType
  xhr.onload = function () {
    settings.callback.apply(xhr, settings.args)
  }
  xhr.open(settings.method, url, true)
  xhr.setRequestHeader('Content-type', settings.contentType)
  // send request
  xhr.send(settings.data)
}

export const file = function (handle, settings) {
  const fr = new window.FileReader()
  // insert defaults
  settings.readAs = settings.readAs || 'text'
  settings.args = settings.args || []
  settings.args.unshift(fr)
  // setup fr
  fr.onload = function () {
    settings.callback.apply(fr, settings.args)
  }
  // read file
  switch (settings.readAs) {
    case 'text':
      fr.readAsText(handle)
      break
    case 'arrayBuffer':
      fr.readAsArrayBuffer(handle)
      break
  }
}
