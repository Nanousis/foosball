<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Record Game</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
</head>
<body class="p-4">
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
  <div class="btn-primary mb-4">
    <a href="{{ route('home') }}" class="btn btn-primary">Home</a>
  </div>
  <h1 class="text-center">Record Game</h1>

  <div class="container">
    <form action="{{ route("games.store") }}" method="POST" class="mb-4">
      @csrf
      <div class="row">
        <!-- Team 1 -->
        <div class="col-md-6">
          <h4 class="mb-3">Team 1</h4>
          @for ($i = 0; $i < 2; $i++)
          <div class="mb-3">
            <label class="form-label">Player {{ $i + 1 }}</label>
            <select class="form-select" name="team1[]">
              <option value="">Select player</option>
              @foreach ($players as $player)
                <option value="{{ $player->id }}">{{ $player->name }}</option>
              @endforeach
            </select>
          </div>
          @endfor

          <div class="mb-4">
            <label class="form-label">Team 1 Score</label>
            <input type="number" class="form-control" name="team1_score" required min="0">
          </div>
        </div>

        <!-- Team 2 -->
        <div class="col-md-6">
          <h4 class="mb-3">Team 2</h4>
          @for ($i = 0; $i < 2; $i++)
          <div class="mb-3">
            <label class="form-label">Player {{ $i + 1 }}</label>
            <select class="form-select" name="team2[]">
              <option value="">Select player</option>
              @foreach ($players as $player)
                <option value="{{ $player->id }}">{{ $player->name }}</option>
              @endforeach
            </select>
          </div>
          @endfor

          <div class="mb-4 ">
            <label class="form-label">Team 2 Score</label>
            <input type="number" class="form-control" name="team2_score" required min="0">
          </div>
        </div>
      </div>
      <div class="mb-4 ">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control form-control-sm mt-2" placeholder="" required>
      </div>
      <button type="submit" class="btn btn-primary mt-2">Record Game</button>
    </form>
  </div>
  <script>
    setTimeout(() => {
      const alert = document.querySelector('.alert-success');
      if (alert) {
        alert.remove();
      }
    }, 3000); // 1 seconds
  </script>
</body>
</html>
