@extends('layouts.app')

@section('title', 'Foosball Players')

@section('content')

  <h1 class="text-center fw-bold mb-2">
    <span class="gradient-text">Foosball</span> Players
  </h1>

  {{-- Player Cards --}}
  <div class="container d-flex justify-content-center align-items-center mb-4 flex-wrap">
    @foreach ($players as $player)
      <div class="card m-2" style="width: 18rem;">
        <img src="{{ asset('storage/' . $player->avatar) }}" class="card-img-top" alt="{{ $player->name }}" style="aspect-ratio: 1 / 1; object-fit: cover; width: 100%; height: 18rem;">
        <div class="card-body">
          <h5 class="card-title">{{ $player->name }}</h5>
          <p class="card-text">Wins: {{ $player->wins }}</p>
          <p class="card-text">Loses: {{ $player->losses }}</p>
          <p class="card-text">Elo: {{ $player->elo }}</p>

          <form action="{{ route('players.delete', $player->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this player?');">
            @csrf
            @method('DELETE')
            <input type="password" name="password" class="form-control form-control-sm mt-2" placeholder="Password" required>
            <button type="submit" class="btn btn-danger btn-sm mt-2">Delete</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Registration Form --}}
  <div class="container">
    <h2 class="mb-4">Player Registration</h2>
    <p>Register a new player by entering their name and uploading an image.</p>

    <form action="{{ route('players.register_user') }}" method="POST" class="mb-4" enctype="multipart/form-data">
      @csrf
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>

      <div class="mb-3">
        <label for="avatar" class="form-label">Upload Image</label>
        <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
      </div>

      <input type="password" name="password" class="form-control form-control-sm my-2" placeholder="Password" required>
      <button type="submit" class="btn btn-primary my-3">Register</button>
    </form>
  </div>
@endsection
