<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>The baby foosball page</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <style>
    th.sortable {
      cursor: pointer;
    }
    th.sortable:after {
      content: ' \25B2'; /* up arrow */
      float: right;
      opacity: 0.5;
    }
    th.sortable.desc:after {
      content: ' \25BC'; /* down arrow */
    }
  </style>
</head>
<body class="p-4">
  <div class="btn-primary mb-4">
    <a href="{{ route('home') }}" class="btn btn-primary">Home</a>
  </div>
  <h1 class="text-center">Foosball Players</h1>
  <div class="container d-flex justify-content-center align-items-center mb-4">
    @foreach ($players as $player)
      <div class="card m-2" style="width: 18rem;">
        <img src="{{ asset('storage/' . $player->avatar) }}" class="card-img-top" alt="{{ $player->name }}" style="aspect-ratio: 1 / 1; object-fit: cover; width: 100%; height: 18rem;">
        <div class="card-body">
          <h5 class="card-title">{{ $player->name }}</h5>
          <p class="card-text">Wins: {{ $player->wins}}</p>
          <p class="card-text">Loses: {{ $player->wins}}</p>
          <p class="card-text">Elo: N/A</p>
          <form action="{{ route('players.delete', $player->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this player?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm mt-2">Delete</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>
  
  <div class="container">
    <h2 class="mb-4">Player Registration</h2>
    <p>Register a new player by entering their name and uploading an image.</p>
    <form action="/register_user" method="POST" class="mb-4" enctype="multipart/form-data">
      @csrf
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
        <div class="mt-3">
          <label for="avatar" class="form-label">Upload Image</label>
          <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Register</button>
    </form>
  </div>
</body>
</html>
