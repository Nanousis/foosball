# Foosball Web App

A fun and competitive web application to track foosball matches, player statistics, and Elo rankings.

## Features

- 🏆 **Elo Ranking System**  
  Automatically updates player rankings after each match based on the Elo algorithm.

- 📊 **Player Statistics**  
  Displays wins, losses, win rate, and current Elo for each player.

- 🕹️ **Match History**  
  View a chronological log of all recorded matches, including players, winners, and Elo changes.
  
- 📸 **Avatar Uploads**  
  Players can upload avatars to personalize their profiles.

- 🧮 **Win/Loss Visuals**  
  Matches and stats use color-coded highlights (green for winners, red for losers) for quick visual feedback.

- 📱 **Responsive UI**  
  Clean, mobile-friendly Bootstrap interface.

## Tech Stack

- **Backend**: Laravel (PHP)
- **Frontend**: Blade + Bootstrap 5
- **Database**: MySQL
- **Storage**: Laravel file storage (public disk for avatars)

## Setup Instructions

### 1. Clone the repository

```bash
git clone https://github.com/nanousis/foosball.git
cd foosball
```

Then change the .env on your database, add 

```APP_PASSWORD = yourpassword```

for the password and do 

```bash
php artisan migrate
php artisan serve
```
