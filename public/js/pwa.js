const swRegisterSuccess = (registration) => {
  console.log('ServiceWorker registration successful with scope: ', registration.scope)
}

const swRegisterError = (error) => {
  console.log('ServiceWorker registration failed: ', error)
}

const registerServiceWorker = () => {
  navigator.serviceWorker.register('/sw.js').then(swRegisterSuccess, swRegisterError)
}

if ('serviceWorker' in navigator) {
  window.addEventListener('load', registerServiceWorker)
}
