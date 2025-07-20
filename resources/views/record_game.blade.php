@extends('layouts.app')

@section('title', 'Record Game')
@push('head')
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/record_game.css') }}" type="text/css">
@endpush
@section('content')

  @if (session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <strong>There were some problems with your input:</strong>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  <h1 class="text-center mb-3 display-2 fw-light">Record Game</h1>
  <hr>
  <div class="container">
    <form action="{{ route("games.store") }}" method="POST" class="mb-4">
      @csrf
      <div class="row">
        <!-- Team 1 -->
        <div class="col-6">
          <h4 class="mb-3 fs-4 gradient-text fw-bolder">Team Blue</h4>
          @for ($i = 0; $i < 2; $i++)
            <div class="mb-3">
              <label class="form-label fs-5">Player {{ $i + 1 }}</label>
              <select class="form-select player-select team1-player" name="team1[]">
                <option value="">Select player</option>
                @foreach ($players as $player)
                  <option value="{{ $player->id }}" data-avatar="{{ asset('storage/' . $player->avatar) }}">
                    {{ $player->name }}
                  </option>
                @endforeach
              </select>
            </div>
          @endfor

          <div class="mb-4">
            <label class="form-label">Score</label>
            <input type="range" class="form-range slider-blue" name="team1_score" min="-2" max="10" value="0" step="1" oninput="team1ScoreOutput.value = this.value">
            <output id="team1ScoreOutput" class="ms-2">0</output>
          </div>
        </div>

        <!-- Team 2 -->
        <div class="col-6">
          <h4 class="mb-3 fs-4 gradient-text-danger fw-bolder">Team Red</h4>
          @for ($i = 0; $i < 2; $i++)
            <div class="mb-3">
              <label class="form-label fs-5">Player {{ $i + 1 }}</label>
              <select class="form-select player-select team2-player" name="team2[]">
                <option value="">Select player</option>
                @foreach ($players as $player)
                  <option value="{{ $player->id }}" data-avatar="{{ asset('storage/' . $player->avatar) }}">
                    {{ $player->name }}
                  </option>
                @endforeach
              </select>
            </div>
          @endfor

          <div class="mb-4">
            <label class="form-label">Score</label>
            <input type="range" class="form-range slider-red" name="team2_score" min="-2" max="10" value="0" step="1" oninput="team2ScoreOutput.value = this.value">
            <output id="team2ScoreOutput" class="ms-2">0</output>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <h3 class="mb-3 fw-light">Statistics</h3>
        <hr>
        <p> <span class="gradient-text"> Expected Winner:</span> <span class="winner text-secondary"></span></p>
        <p> <span class="gradient-text-danger"> Expected Loser:</span> <span class="looser text-secondary"></span></p>
        <p> <span class=""> Min score to gain ELO:</span> <span class="min_score text-secondary"></span></p>
      </div>
      <hr>
      <div class="mb-4">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control form-control-sm mt-2" placeholder="Password" required>
      </div>
      <button type="submit" class="btn btn-primary mt-2">Record Game</button>
    </form>
  </div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/record_game.js') }}"></script> {{-- Put this last if it needs jQuery --}}
<script>
$(function () {
  function checkAllSelected() {
    const values = [
      $('.team1-player').eq(0).val(),
      $('.team1-player').eq(1).val(),
      $('.team2-player').eq(0).val(),
      $('.team2-player').eq(1).val()
    ];
    const allSelected = values.every(v => v !== '');
    const api_route = '{{ route("api.preview-game") }}';
    console.log("api_route:", api_route);
    if (allSelected) {
      const team1 = values.slice(0, 2);
      const team2 = values.slice(2, 4);
      fetch(api_route, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ team1, team2 })
      })
      .then(res => res.json())
      .then(data => {
        $('.winner').text(data.winner);
        $('.looser').text(data.loser);
        $('.min_score').text(data.min_score);
      })
      .catch(err => console.error('API Error:', err));
    }
  }
  $('.team1-player, .team2-player').on('change', checkAllSelected);
});


</script>
@endpush
