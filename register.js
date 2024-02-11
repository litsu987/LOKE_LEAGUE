document.addEventListener("DOMContentLoaded", function () {
  var password = document.getElementById("password2");
  var confirmPassword = document.getElementById("confirmPassword2");

  function validatePassword() {
    var passwordValue = password.value;

    // Expresiones regulares para verificar la presencia de mayúsculas, números y caracteres especiales
    var hasUpperCase = /[A-Z]/.test(passwordValue);
    var hasLowerCase = /[a-z]/.test(passwordValue);
    var hasDigit = /\d/.test(passwordValue);
    var hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(passwordValue);

    // Validar que cumple con los requisitos
    if (!hasUpperCase || !hasLowerCase || !hasDigit || !hasSpecialChar) {
      password.setCustomValidity("La contraseña debe contener al menos una mayúscula, un número y un carácter especial");
    } else {
      password.setCustomValidity("");
      validatePasswordMatch();  // Asegurarse de que las contraseñas coincidan
    }
  }

  function validatePasswordMatch() {
    if (password.value !== confirmPassword.value) {
      confirmPassword.setCustomValidity("Las contraseñas no coinciden");
    } else {
      confirmPassword.setCustomValidity("");
    }
  }

  // Agregar eventos de entrada para ambas funciones de validación
  password.addEventListener("input", validatePassword);
  confirmPassword.addEventListener("input", validatePasswordMatch);
});


function showNotification(type, message) {
  var notificationContainer = $(`
      <div class="notification-container"></div>
    `);

  var notification = $(`
      <div class="notification ${type}">${message}</div>
    `);

  var closeIcon = $(`
      <span class="close-icon">x</span>
    `);
  closeIcon.on('click', function () {
    closeNotification(notificationContainer);
  });

  notification.append(closeIcon);
  notificationContainer.append(notification);
  $('#notificationContainer').append(notificationContainer);
  notification.css('display', 'block');
  notification.hide().slideDown();
}

function closeNotification(notificationContainer) {
  notificationContainer.css('display', 'none');
  notificationContainer.remove();
}
//showNotification('warning', 'Funciono correctamentedasdasdasdasdasdasdsadddanfidsnfonasoifdniofdifasfndaslfdsafasfndasfiaunfl');
//showNotification('info', 'Mensaje de error');
