<!-- Author: Tang Lit Xuan -->
<!-- API Token Management -->
@if(session('api_token'))
<script>
    // Store token in localStorage when login is successful
    (function() {
        const token = @json(session('api_token'));
        if (token) {
            localStorage.setItem('api_token', token);
            // Clear from session after storing
            fetch('{{ route("auth.clear-token") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            }).catch(function() {
                // Silently handle error
            });
        }
    })();
</script>
@endif

@if(session('clear_token'))
<script>
    // Clear token from localStorage when logout
    localStorage.removeItem('api_token');
  </script>
@endif

<script>
    // Handle logout form submission - clear token before submitting
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            // Find all logout forms
            const logoutForms = document.querySelectorAll('form[action*="logout"]');
            
            logoutForms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    // Clear token from localStorage before form submission
                    localStorage.removeItem('api_token');
                });
            });
        });
    })();
</script>

