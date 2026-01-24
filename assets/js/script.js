document.addEventListener('DOMContentLoaded', function () {

    // Live Search Functionality
    const searchInput = document.getElementById('search-quiz');
    const difficultyFilter = document.getElementById('filter-difficulty');
    const quizGrid = document.getElementById('quiz-grid');

    function fetchQuizzes() {
        if (!quizGrid) return;

        const query = searchInput ? searchInput.value : '';
        const difficulty = difficultyFilter ? difficultyFilter.value : '';

        const formData = new FormData();
        formData.append('query', query);
        formData.append('difficulty', difficulty);

        fetch('search.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(html => {
                quizGrid.innerHTML = html;
            })
            .catch(err => console.error('Error fetching quizzes:', err));
    }

    if (searchInput) {
        searchInput.addEventListener('input', fetchQuizzes);
    }
    if (difficultyFilter) {
        difficultyFilter.addEventListener('change', fetchQuizzes);
    }

    // Quiz Timer functionality
    const timerElement = document.getElementById('timer');
    if (timerElement) {
        let duration = parseInt(timerElement.dataset.duration) * 60; // minutes to seconds

        function updateTimer() {
            const minutes = Math.floor(duration / 60);
            const seconds = duration % 60;

            timerElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

            if (duration <= 0) {
                clearInterval(timerInterval);
                alert("Time is up! Submitting your quiz.");
                document.getElementById('quiz-form').submit(); // Or trigger AJAX submit
            } else {
                duration--;
            }
        }

        // Initial call
        updateTimer();
        const timerInterval = setInterval(updateTimer, 1000);
    }

    // AJAX Quiz Submission  
    const quizForm = document.getElementById('quiz-form');
    if (quizForm) {
        quizForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Disable button
            const btn = quizForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Submitting...';

            const formData = new FormData(quizForm);

            fetch('submit.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.status === 'success') {
                            window.location.href = `result.php?attempt_id=${data.attempt_id}`;
                        } else {
                            alert('Error submitting quiz: ' + data.message);
                            btn.disabled = false;
                            btn.textContent = 'Submit Answers';
                        }
                    } catch (e) {
                        console.error('Server Error:', text);
                        alert('Server Error: ' + text.substring(0, 100) + '...'); // Show snippet to user
                        btn.disabled = false;
                        btn.textContent = 'Submit Answers';
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('An unexpected error occurred.');
                    btn.disabled = false;
                    btn.textContent = 'Submit Answers';
                });
        });
    }
});
