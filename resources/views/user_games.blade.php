@extends('layouts.app')

@section('title', 'Foosball Rankings')

@section('content')

<div class="d-flex justify-content-center my-4">
    <div class="card m-2" style="width: 18rem;">
        <img src="{{ asset('storage/' . $player->avatar) }}" class="card-img-top" alt="{{ $player->name }}" style="aspect-ratio: 1 / 1; object-fit: cover; width: 100%; height: 18rem;">
        <div class="card-body">
            <h5 class="card-title">{{ $player->name }}</h5>
            <p class="card-text">Wins: {{ $player->wins }}</p>
            <p class="card-text">Loses: {{ $player->losses }}</p>
            <p class="card-text">Elo: {{ $player->elo }}</p>
        </div>
    </div>
</div>

  {{-- Match History --}}
  <h1 class="text-center mb-3">Match History</h1>
  <div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
      <thead class="">
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
                  <a href="/users/{{ $player->id }}/games" class="text-decoration-none text-reset">{{ $player->name }}</a>
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
