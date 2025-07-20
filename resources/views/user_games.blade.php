@extends('layouts.app')

@section('title', 'Foosball Rankings')

@section('content')

<div class="row my-4 gx-4">
  {{-- Player Card (Left on desktop, top on mobile) --}}
  <div class="col-12 col-md-4  d-flex d-md-block  justify-content-center">
    <div class="card my-5 w-100" style="max-width: 18rem;">
      <img src="{{ asset('storage/' . $player->avatar) }}" class="card-img-top" alt="{{ $player->name }}"
           style="aspect-ratio: 1 / 1; object-fit: cover; width: 100%; height: 18rem;">
      <div class="card-body">
        @php
          $winrate = $player->games_played > 0 ? ($player->wins / $player->games_played) * 100 : 0;
        @endphp
        <h3 class="card-title fw-bold">{{ $player->name }}</h3>
        <hr>
        <p class="card-text">Wins: <span class="text-success">{{ $player->wins }}</span></>
        <p class="card-text">Loses: <span class="text-danger">{{ $player->losses }}</span></>
        <p class="card-text">Elo: <span class="fw-bold">{{ round($player->elo) }}</span></>
        <p class="card-text">
          Winrate: <span class="fw-bold {{ $winrate >= 60 ? 'text-success' : ($winrate >= 40 ? 'text-warning' : 'text-danger') }}">
            {{ number_format($winrate, 0) }}%
          </span>
        </>
      </div>
    </div>
  </div>

  {{-- Match History Table (Right on desktop, bottom on mobile) --}}
  <div class="col-12 col-md-8">
    <h1 class="text-center mb-3">Match History</h1>
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead>
          <tr class="header-primary">
            <th class="d-none d-md-table-cell">Date</th>
            <th>Winners</th>
            <th class="text-center">Score</th>
            <th>Losers</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($games as $game)
          <tr>
            <td class="d-none d-md-table-cell result-{{ $game->result }}">
              {{ $game->created_at->setTimezone('Europe/Paris')->format('M d, Y - H:i') }}
            </td>

            {{-- Winners --}}
            <td class="result-{{ $game->result }}">
              @php
                $winnerPlayers = [[$game->winner1, $game->winner1_elo_change ?? 0], [$game->winner2, $game->winner2_elo_change ?? 0]];
              @endphp
              @foreach ($winnerPlayers as [$player, $change])
                @if ($player)
                  <div class="d-flex align-items-center mb-1">
                    <img src="{{ asset('storage/' . $player->avatar) }}" alt="{{ $player->name }}"
                         style="width: 24px; height: 24px; object-fit: cover; border-radius: 50%; margin-right: 6px;">
                    <a href="{{ route('players.games', ['id' => $player->id]) }}" class="text-decoration-none text-reset">{{ $player->name }}</a>
                    <span class="ms-1 text-success">(+{{ round($change) }})</span>
                  </div>
                @endif
              @endforeach
            </td>

            {{-- Score --}}
            <td class="text-center align-middle result-{{ $game->result }}">
              {{ $game->winner_score }} - {{ $game->loser_score }}
            </td>
            
            {{-- Losers --}}
            <td class="result-{{ $game->result }}">
              @php
                $loserPlayers = [[$game->loser1, $game->loser1_elo_change ?? 0], [$game->loser2, $game->loser2_elo_change ?? 0]];
              @endphp
              @foreach ($loserPlayers as [$player, $change])
                @if ($player)
                  <div class="d-flex align-items-center mb-1">
                    <img src="{{ asset('storage/' . $player->avatar) }}" alt="{{ $player->name }}"
                         style="width: 24px; height: 24px; object-fit: cover; border-radius: 50%; margin-right: 6px;">
                    <a href="/users/{{ $player->id }}/games" class="text-decoration-none text-reset">{{ $player->name }}</a>
                    <span class="ms-1 text-danger">({{ round($change) }})</span>
                  </div>
                @endif
              @endforeach
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll("th.sortable").forEach((header, index) => {
    header.addEventListener("click", () => {
      const table = header.closest("table");
      const tbody = table.querySelector("tbody");
      const rows = Array.from(tbody.querySelectorAll("tr"));
      const isDescending = header.classList.contains("desc");

      rows.sort((a, b) => {
        const cellA = a.children[index].textContent.trim();
        const cellB = b.children[index].textContent.trim();
        const isNumeric = !isNaN(cellA) && !isNaN(cellB);

        return isNumeric
        ? (isDescending ? cellB - cellA : cellA - cellB)
        : (isDescending
        ? cellB.localeCompare(cellA)
        : cellA.localeCompare(cellB));
      });

      table.querySelectorAll("th").forEach(th => th.classList.remove("desc"));
      if (!isDescending) header.classList.add("desc");
      rows.forEach(row => tbody.appendChild(row));
    });
  });
</script>

@endsection
