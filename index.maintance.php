<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Under Maintenance</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
    font-family: 'Roboto', sans-serif;
    background-color: #f4f4f4; /* Light gray background */
    color: #333; /* Dark gray text */
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh; /* Full viewport height */
    margin: 0;
    padding: 20px; /* Padding for small screens */
    box-sizing: border-box; /* Include padding in element's total width and height */
}

.container {
    background-color: #ffffff; /* White content box */
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    padding: 40px;
    max-width: 600px; /* Max width for content */
    width: 100%; /* Full width on smaller screens */
}

h1 {
    color: #0056b3; /* A nice blue for the heading */
    font-size: 2.5em;
    margin-bottom: 20px;
}

p {
    font-size: 1.1em;
    line-height: 1.6;
    margin-bottom: 15px;
}

.contact-info {
    margin-top: 30px;
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.contact-info ul {
    list-style: none;
    padding: 0;
    margin: 15px 0;
    display: flex;
    justify-content: center;
    gap: 20px; /* Space between social links */
    flex-wrap: wrap; /* Allow links to wrap on smaller screens */
}

.contact-info li {
    display: inline-block; /* For older browsers, though flexbox handles it */
}

.contact-info a {
    color: #007bff; /* Blue for links */
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s ease;
}

.contact-info a:hover {
    color: #0056b3; /* Darker blue on hover */
    text-decoration: underline;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    h1 {
        font-size: 2em;
    }

    .container {
        padding: 30px;
    }

    .contact-info ul {
        flex-direction: column; /* Stack social links on small screens */
        gap: 10px;
    }
}

@media (max-width: 480px) {
    h1 {
        font-size: 1.8em;
    }

    p {
        font-size: 1em;
    }

    .container {
        padding: 20px;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h1>We'll be back soon!</h1>
            <p>Sorry for the inconvenience but we're performing some maintenance at the moment. We'll be back online shortly!</p>
        </div>
    </div>
</body>
</html>