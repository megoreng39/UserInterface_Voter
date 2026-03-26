<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BulSU Live Election Tally</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f4f7f6; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            padding: 40px; 
        }
        .container { 
            width: 90%; 
            max-width: 900px; 
            background: white; 
            padding: 30px; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            text-align: center;
        }
        h2 { color: #2c3e50; margin-bottom: 5px; }
        .live-indicator { 
            color: #27ae60; 
            font-weight: bold; 
            font-size: 0.8rem; 
            margin-bottom: 25px; 
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🗳️ Election Results</h2>
    <div class="live-indicator">● LIVE UPDATING FROM DATABASE</div>
    
    <canvas id="votingChart"></canvas>
</div>

<script>
    let myChart;

    // Distinct colors for your candidates (Vince, Roni, etc.)
    const barColors = [
        '#3498db', // Blue
        '#e91e63', // Pink
        '#1abc9c', // Teal
        '#f1c40f', // Yellow
        '#9b59b6', // Purple
        '#e67e22', // Orange
        '#2ecc71', // Green
        '#e74c3c'  // Red
    ];

    async function fetchAndRefresh() {
        try {
            // This fetches data from your data.php file
            const response = await fetch('data.php');
            const data = await response.json();

            const names = data.map(item => item.name);
            const votes = data.map(item => item.votes);

            if (!myChart) {
                // FIRST TIME INITIALIZATION
                const ctx = document.getElementById('votingChart').getContext('2d');
                myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: names,
                        datasets: [{
                            label: 'Votes',
                            data: votes,
                            backgroundColor: barColors,
                            borderRadius: 8,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                suggestedMax: 20, // Forces the initial 5-10-15-20 look
                                ticks: {
                                    stepSize: 5 // Sets the gap between numbers
                                }
                            }
                        },
                        animation: {
                            duration: 1000 // Smooth bar growth
                        }
                    }
                });
            } else {
                // REFRESH DATA WITHOUT BLINKING
                myChart.data.labels = names;
                myChart.data.datasets[0].data = votes;
                myChart.update('none'); // Update smoothly every 2 seconds
            }
        } catch (error) {
            console.error("Error: Check your data.php or database connection!", error);
        }
    }

    // Refresh every 2 seconds so you see votes as they happen
    setInterval(fetchAndRefresh, 2000);

    // Run once immediately on page load
    fetchAndRefresh();
</script>

</body>
</html>