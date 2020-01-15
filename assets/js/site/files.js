// the files div
const filesDiv = document.getElementById('files')

// initialise the files div
function init (data) {
  const fragment = document.createDocumentFragment()
  data.directories.forEach((directory) => {
    fragment.appendChild(viewDirectory(directory))
  })
  data.files.forEach((file) => {
    fragment.appendChild(viewFile(file))
  })
  filesDiv.innerHTML = ''
  filesDiv.appendChild(fragment)
}

// create HTML div for showing a directory
function viewDirectory (directory) {
  const div = document.createElement('div')
  const header = document.createElement('div')
  const content = document.createElement('div')
  header.classList.add('file')
  header.innerHTML = `<i class="far fa-folder"></i><span class="text">${directory.path}</span>`
  header.addEventListener('click', () => {
    if (content.classList.contains('active')) {
      content.classList.remove('active')
      header.innerHTML = `<i class="far fa-folder"></i><span class="text">${directory.path}</span>`
    } else {
      content.classList.add('active')
      header.innerHTML = `<i class="far fa-folder-open"></i><span class="text">${directory.path}</span>`
    }
  })
  content.classList.add('content')
  directory.directories.forEach((directory) => {
    content.appendChild(viewDirectory(directory))
  })
  directory.files.forEach((file) => {
    content.appendChild(viewFile(file))
  })
  div.classList.add('directory')
  div.appendChild(header)
  div.appendChild(content)
  return div
}

// create HTML div for showing a file
function viewFile (file) {
  const div = document.createElement('div')
  const header = document.createElement('a')
  header.innerHTML = `<i class="far fa-file-code"></i>${file}`
  header.href = '' // TODO
  div.classList.add('file')
  div.appendChild(header)
  return div
}

// bootstrap
if (filesDiv) {
  window.fetch('/account/files/json').then((response) => {
    response.json().then((data) => {
      init(data)
    })
  })
}
