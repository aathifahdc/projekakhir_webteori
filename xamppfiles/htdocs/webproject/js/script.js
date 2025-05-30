function validateLogin() {
  const username = document.querySelector('input[name="username"]').value;
  if (username.length < 3) {
    alert("Username must be at least 3 characters long");
    return false;
  }
  return true;
}
