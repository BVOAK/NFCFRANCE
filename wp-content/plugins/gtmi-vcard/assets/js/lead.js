document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('gtmi-vcard-lead-exchange')
    if(form) {
      const responseMessage = document.getElementById('form-response-message')
      // gtmiVCardLeadExchange object defined back-end /utils
      if (form && typeof gtmiVCardLeadExchange !== 'undefined') {
          form.addEventListener('submit', function(event) {
              event.preventDefault()
              responseMessage.textContent = 'Running...'
              responseMessage.style.color = 'orange'
              const formData = new FormData(form) // Get all form data
              fetch(gtmiVCardLeadExchange.restUrl, {
                method: 'POST',
                body: formData,
                headers: {
                  'X-WP-Nonce': gtmiVCardLeadExchange.nonce
                },
              })
              .then(response => {
                if (!response.ok || response.status != 201) {
                  throw new Error('Network response was not ok')
                }
                return response.json()
              })
              .then(data => {
                  if (data.success) {
                      responseMessage.textContent = data.message
                      responseMessage.style.color = 'green'
                      form.reset()
                  } else {
                      responseMessage.textContent = `Error : ${data.message}`
                      responseMessage.style.color = 'red'
                  }
              })
              .catch(error => {
                  console.error('Error ', error)
                  responseMessage.textContent = 'Internal error'
                  responseMessage.style.color = 'red'
              })
          })
      }
    }
})