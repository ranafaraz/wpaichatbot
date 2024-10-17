jQuery(document).ready(function($) {
    // Initialize conversation history
    let conversationHistory = [];

    // Initially hide the chat body
    $('#chatgpt-body').hide();

    // Toggle chat widget when the header is clicked
    $('#chatgpt-header').click(function() {
        $('#chatgpt-body').toggle();
    });

    // Function to send the message
    function sendMessage() {
        var message = $('#chatgpt-input').val();
        if (!message.trim()) {
            return;
        }
        $('#chatgpt-messages').append('<div class="chatgpt-message chatgpt-message-user">' + message + '</div>');

        // Add user message to conversation history
        conversationHistory.push({ role: 'user', content: message });

        $.ajax({
            url: chatgpt_ajax.url,
            type: 'POST',
            data: {
                action: 'chatgpt_request',
                message: message,
                history: JSON.stringify(conversationHistory),  // Send history with the request
                security: chatgpt_ajax.security
            },
            success: function(response) {
                if (response.success) {
                    $('#chatgpt-messages').append('<div class="chatgpt-message chatgpt-message-bot">' + response.data + '</div>');
                    // Add bot message to conversation history
                    conversationHistory.push({ role: 'assistant', content: response.data });
                } else {
                    $('#chatgpt-messages').append('<div class="chatgpt-message chatgpt-message-error">' + response.data + '</div>');
                }
                $('#chatgpt-input').val('');
                $('#chatgpt-messages').scrollTop($('#chatgpt-messages')[0].scrollHeight);  // Ensure scrolls to bottom
            },
            error: function(xhr, status, error) {
                console.error('Error: ' + error);
                $('#chatgpt-messages').append('<div class="chatgpt-message chatgpt-message-error">An error occurred</div>');
                $('#chatgpt-messages').scrollTop($('#chatgpt-messages')[0].scrollHeight);  // Ensure scrolls to bottom
            }
        });
    }

    // Handle send button click
    $('#chatgpt-send').click(function() {
        sendMessage();
    });

    // Trigger send button click on Enter key press
    $('#chatgpt-input').keypress(function(event) {
        if (event.which === 13) {
            event.preventDefault();
            sendMessage();
        }
    });
});
