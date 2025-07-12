<nav class="navbar navbar-expand-md navbar-light bg-light shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="{{ route('home') }}">
      <div class="text-center">
        <h1 class="fw-bold m-0">
          <span class="gradient-text">arm</span>
          <span>Foosball</span>
        </h1>
      </div>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="{{ route('players.register') }}">Register</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('games.store') }}">Record Game</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
