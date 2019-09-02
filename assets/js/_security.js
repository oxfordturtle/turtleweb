import * as dom from './_dom'

const loginRequestResponse = function (xhr) {
  if (xhr.response.success) {
    window.location.reload()
  } else {
    console.log(xhr.response)
    dom.add(dom.parent(dom.id('username')), 'has-errors')
    dom.remove(dom.id('loginError'), 'hidden')
    dom.remove(dom.get('body'), 'waiting')
  }
}

const login = function (event) {
  var node = event.currentTarget
  event.preventDefault()
  dom.remove(dom.parent(dom.id('username')), 'has-errors')
  dom.add(dom.id('loginError'), 'hidden')
  dom.add(dom.get('body'), 'waiting')
  dom.ajax(node.action, {
    method: 'POST',
    data: dom.serialize(node),
    responseType: 'json',
    callback: loginRequestResponse
  })
}

const logoutRequestResponse = function () {
  window.location.reload()
}

const logout = function (event) {
  var node = event.currentTarget
  event.preventDefault()
  dom.ajax(node.href, { callback: logoutRequestResponse })
}

const emailCredentialsRequestResponse = function (xhr) {
  dom.remove(dom.get('body'), 'waiting')
  if (xhr.response.success) {
    dom.add(dom.id('emailCredentialsForm'), 'hidden')
    dom.remove(dom.id('emailCredentialsSuccess'), 'hidden')
  } else {
    dom.html(dom.id('emailCredentialsForm'), xhr.response.form)
    dom.each(dom.all('[data-action]', dom.id('emailCredentialsForm')), addAction)
  }
}

const emailCredentials = function (event) {
  var node = event.currentTarget
  event.preventDefault()
  dom.add(dom.get('body'), 'waiting')
  dom.ajax(node.action, {
    method: 'POST',
    data: dom.serialize(node),
    responseType: 'json',
    callback: emailCredentialsRequestResponse
  })
}

const addAction = function (node) {
  switch (dom.data(node, 'action')) {
    case 'login':
      dom.on(node, 'submit', login)
      break
    case 'logout':
      dom.on(node, 'click', logout)
      break
    case 'email-credentials':
      dom.on(node, 'submit', emailCredentials)
      break
  }
}

dom.each(dom.all('[data-action]'), addAction)
