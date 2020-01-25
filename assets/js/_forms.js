import * as dom from './_dom'

const submitMatchValue = function (submit, value) {
  if (value) {
    submit.removeAttribute('disabled')
  } else {
    submit.disabled = 'disabled'
  }
}

const registrationSubmitMatchValue = function () {
  var submit = dom.id('registerButton')
  if (submit) {
    submitMatchValue(submit, dom.value(dom.id('agreeToTerms')))
  }
}

const toggleSubmit = function (event) {
  var box = event.currentTarget
  var submit = dom.id(dom.data(box, 'target'))
  submitMatchValue(submit, dom.value(box))
}

const showSchoolData = function (xhr, name, postcode) {
  dom.value(name, xhr.response.name)
  dom.value(postcode, xhr.response.postcode)
}

const schoolFromUrn = function (event) {
  var input = event.currentTarget
  var path
  var name
  var postcode
  if (dom.value(input).toString(10).length === 6) {
    path = dom.data(input, 'urn-path') + '/'
    name = dom.id(dom.data(input, 'name'))
    postcode = dom.id(dom.data(input, 'postcode'))
    dom.ajax(path + dom.value(input), {
      responseType: 'json',
      args: [name, postcode],
      callback: showSchoolData
    })
  }
}

const addAction = function (node) {
  switch (dom.data(node, 'action')) {
    case 'toggle-submit':
      dom.on(node, 'change', toggleSubmit)
      break
    case 'school-from-urn':
      dom.on(node, 'keyup', schoolFromUrn)
      break
  }
}

registrationSubmitMatchValue()
dom.each(dom.all('[data-action]'), addAction)

dom.each(dom.all('[data-disabled]'), (button) => {
  const checkboxes = Array.from(dom.all(`[data-enable="${button.dataset.disabled}"]`))
  const update = () => {
    if (checkboxes.every(x => x.checked)) {
      button.removeAttribute('disabled')
    } else {
      button.setAttribute('disabled', 'disabled')
    }
  }
  update()
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', update)
  })
})
