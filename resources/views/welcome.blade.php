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
  <div class="container">
    <h2 class="mb-4">Player Stats</h2>
    <table id="playerTable" class="table table-bordered table-striped table-hover">
      <thead class="table-dark">
        <tr>
          <th class="sortable">Name</th>
          <th class="sortable">Wins</th>
          <th class="sortable">Loses</th>
          <th class="sortable">Elo</th>
          <th class="sortable">Avg. Score</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($players as $player)
        <tr>
            <td>
                <img src="{{ asset('storage/' . $player->avatar) }}" alt="{{ $player->name }}" style="width: 32px; height: 32px; object-fit: cover; border-radius: 50%; margin-right: 8px;">
                {{ $player->name }}
            </td>
            <td>{{ $player->wins }}</td>
            <td>{{ $player->losses }}</td>
            <td>{{ $player->elo }}</td>
            <td>
                @if ($player->games_played > 0)
                {{ number_format($player->total_score / $player->games_played, 2) }}
                @else
                0
                @endif
            </td>
        </tr>
    @endforeach
      </tbody>
    </table>
    <div class="btn-primary mb-4">
        <a href="{{ route("games.store") }}" class="btn btn-primary">Record Game</a>
    </div>

    <h2 class="mt-2 mb-4">Match History</h2>
    <table class="table table-bordered table-striped table-hover">
    <thead class="table-dark">
        <tr>
        <th>Date</th>
        <th>Winners</th>
        <th class="text-center">Score</th>
        <th>Losers</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($games as $game)
        <tr>
        {{-- Date --}}
        <td>{{ $game->created_at->format('M d, Y - H:i') }}</td>

        {{-- Winners --}}
        <td class="">
            @php
                $winnerPlayers = [
                    [$game->winner1, $game->winner1_elo_change ?? 0],
                    [$game->winner2, $game->winner2_elo_change ?? 0]
                ];
            @endphp
            @foreach ($winnerPlayers as [$player, $change])
                @if ($player)
                    <div class="d-flex align-items-center mb-1">
                        <img src="{{ asset('storage/' . $player->avatar) }}" alt="{{ $player->name }}"
                            style="width: 24px; height: 24px; object-fit: cover; border-radius: 50%; margin-right: 6px;">
                        {{ $player->name }}
                        <span class="ms-1 text-success">(+{{ round($change) }})</span>
                    </div>
                @endif
            @endforeach
        </td>

        {{-- Score --}}
        <td class="text-center align-middle">
            {{ $game->winner_score }} - {{ $game->loser_score }}
        </td>

        {{-- Losers --}}
        <td class="">
            @php
                $loserPlayers = [
                    [$game->loser1, $game->loser1_elo_change ?? 0],
                    [$game->loser2, $game->loser2_elo_change ?? 0]
                ];
            @endphp
            @foreach ($loserPlayers as [$player, $change])
                @if ($player)
                    <div class="d-flex align-items-center mb-1">
                        <img src="{{ asset('storage/' . $player->avatar) }}" alt="{{ $player->name }}"
                            style="width: 24px; height: 24px; object-fit: cover; border-radius: 50%; margin-right: 6px;">
                        {{ $player->name }}
                        <span class="ms-1 text-danger">({{ round($change) }})</span>
                    </div>
                @endif
            @endforeach
        </td>
        </tr>
        @endforeach
    </tbody>
    </table>


    <div class="btn-secondary mb-4">
        <a href="{{ route("players.register") }}" class="btn btn-secondary">Register Player</a>
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
</body>
</html>
