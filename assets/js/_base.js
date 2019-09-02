import * as dom from './_dom'

const tab = function (event) {
  var node = event.currentTarget
  var target = dom.id(dom.data(node, 'target'))
  var extra = dom.data(node, 'extra')
  var select = dom.data(dom.parent(node), 'select')
  dom.add(node, 'active', true)
  dom.add(target, 'active', true)
  if (extra) {
    dom.add(dom.id(extra), 'active')
  }
  if (select) {
    dom.value(dom.id(select), node.id, true)
  }
  if (dom.get('input', target)) {
    dom.get('input', target).focus()
  }
}

const selectTab = function (event) {
  dom.id(dom.value(event.currentTarget)).click()
}

const dropdown = function (event) {
  var node = event.currentTarget
  event.stopPropagation()
  dom.toggle(dom.parent(node), 'active')
}

const modal = function (event) {
  var node = event.currentTarget
  var target = dom.id(dom.data(node, 'target'))
  event.preventDefault()
  if (dom.has(target, 'active')) {
    dom.remove(document.body, 'modal-open')
    dom.remove(target, 'active')
  } else {
    dom.add(document.body, 'modal-open')
    dom.add(target, 'active')
    if (dom.get('input', target)) {
      dom.get('input', target).focus()
    }
  }
}

const deactivate = function (node) {
  dom.remove(node, 'active')
}

const deactivateParent = function (node) {
  dom.remove(dom.parent(node), 'active')
}

const modalClearAll = function (event) {
  if (event.keyCode === 27) {
    dom.remove(document.body, 'modal-open')
    dom.each(dom.all('.modal'), deactivate)
  }
}

const dropdownClearAll = function () {
  dom.each(dom.all('.dropdown'), deactivateParent)
}

const dismissAlert = function (event) {
  dom.remove(dom.parent(event.currentTarget), 'active')
}

const togglePanel = function (event) {
  dom.toggle(dom.parent(event.currentTarget), 'active')
}

const href = function (event) {
  window.location = dom.data(event.currentTarget, 'target')
}

const showHideCourses = function (courseToShow, course) {
  if (dom.data(course, 'course') === courseToShow) {
    dom.add(course, 'active')
  } else {
    dom.remove(course, 'active')
  }
}

const showCourse = function (event) {
  var element = event.currentTarget
  var courseToShow = dom.value(element)
  var group = dom.data(element, 'group')
  var courses = dom.all(`[data-group="${group}"]`)
  dom.each(courses, showHideCourses.bind(null, courseToShow))
}

const addAction = function (node) {
  switch (dom.data(node, 'action')) {
    case 'tab':
      dom.on(node, 'click', tab)
      break
    case 'select-tab':
      dom.on(node, 'change', selectTab)
      break
    case 'dropdown':
      dom.on(node, 'click', dropdown)
      break
    case 'modal':
      dom.on(node, 'click', modal)
      break
    case 'dismiss-alert':
      dom.on(node, 'click', dismissAlert)
      break
    case 'toggle-panel':
      dom.on(node, 'click', togglePanel)
      break
    case 'href':
      dom.on(node, 'click', href)
      break
    case 'show-course':
      dom.on(node, 'click', showCourse)
      break
  }
}

dom.on(document, 'keyup', modalClearAll)
dom.on(document, 'click', dropdownClearAll)
dom.each(dom.all('[data-action]'), addAction)
