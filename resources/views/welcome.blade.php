@extends('layouts.app')

@section('title', 'Foosball Rankings')
@push('head')
  <style>
    .link {
      stroke: #aaa;
      stroke-width: 1.5px;
    }
    text {
      font-size: 10px;
      text-anchor: middle;
      fill: #333;
    }
  </style>
@endpush
@section('content')
  <script src="https://d3js.org/d3.v7.min.js"></script>

  <h1 class="text-center my-3">
    <span class="">Foosball Rankings</span>
  </h1>
  {{-- Player Table --}}
  <div class="table-responsive">
    <table id="playerTable" class="table table-bordered table-striped table-hover">
      <thead class="">
        <tr class="header-primary">
          <th class="sortable">Elo</th>
          <th class="sortable">Name</th>
          <th class="sortable">W-L</th>
          <th class="sortable">Stats</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($players as $player)
        <tr>
          <td><span class="fw-bolder">{{ round($player->elo) }}</span></td>
            <td onclick="window.location.href='{{ route('players.games', ['id' => $player->id]) }}'">
              <a href="{{ route('players.games', ['id' => $player->id]) }}" class="d-flex align-items-center text-decoration-none text-dark">
              <img src="{{ asset('storage/' . $player->avatar) }}" alt="{{ $player->name }}"
                 style="width: 32px; height: 32px; object-fit: cover; border-radius: 50%; margin-right: 8px;">
              {{ $player->name }}
              </a>
            </td>
          <td class="text-center" style="white-space: nowrap;">
            <span class="text-success fw-bolder">{{ $player->wins }}</span> -
            <span class="text-danger fw-bolder">{{ $player->losses }}</span>
          </td>
          <td>
            @php
              $winrate = $player->games_played > 0 ? ($player->wins / $player->games_played) * 100 : 0;
            @endphp
            @if ($player->games_played > 0)
            <div class="d-flex flex-column">
              <span>
                <span class="{{ $winrate >= 60 ? 'text-success' : ($winrate >= 40 ? 'text-warning' : 'text-danger') }}">
                  {{ number_format($winrate, 0) }}%
                </span> W/L
              </span>
              <hr>
              <span>{{ number_format($player->total_score / $player->games_played, 2) }} Goals</span>
            </div>
            @else
              N/A
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>


  <h1 class="text-center my-3">
    <span class="">Player Correlation Graph</span>
  </h1>
  <hr>
  <div class="graph-container" style="width: 100%; max-width: 1000px; margin: auto;">
    <svg id="correlation-graph" style="width: 100%; height: auto;"></svg>
  </div>
  <hr>

  <div class="mb-4">
    <a href="{{ route("games.store") }}" class="btn btn-gradient-dark">Record Game</a>
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
          <td class="d-none d-md-table-cell">{{ $game->created_at->setTimezone('Europe/Paris')->format('M d, Y - H:i') }}</td>

          {{-- Winners --}}
          <td>
            @php
              $winnerPlayers = [[$game->winner1, $game->winner1_elo_change ?? 0], [$game->winner2, $game->winner2_elo_change ?? 0]];
            @endphp
            @foreach ($winnerPlayers as [$player, $change])
              @if ($player)
                <div class="d-flex align-items-center mb-1">
                  <img src="{{ asset('storage/' . $player->avatar) }}" alt="{{ $player->name }}"
                       style="width: 24px; height: 24px; object-fit: cover; border-radius: 50%; margin-right: 6px;">
                  <a href="{{ route('players.games', ['id' => $player->id]) }}" class="text-decoration-none text-reset">{{ $player->name }}</a>

                  @if ($change > 0)
                      <span class="ms-1 text-success">(+{{ round($change) }})</span>
                  @else
                      <span class="ms-1 text-danger">({{ round($change) }})</span>
                  @endif
                </div>
              @endif
            @endforeach
          </td>

          {{-- Score --}}
          <td class="text-center align-middle">
            {{ $game->winner_score }} - {{ $game->loser_score }}
          </td>

          {{-- Losers --}}
          <td>
            @php
              $loserPlayers = [[$game->loser1, $game->loser1_elo_change ?? 0], [$game->loser2, $game->loser2_elo_change ?? 0]];
            @endphp
            @foreach ($loserPlayers as [$player, $change])
              @if ($player)
                <div class="d-flex align-items-center mb-1">
                  <img src="{{ asset('storage/' . $player->avatar) }}" alt="{{ $player->name }}"
                       style="width: 24px; height: 24px; object-fit: cover; border-radius: 50%; margin-right: 6px;">
                  <a href="{{ route('players.games', ['id' => $player->id]) }}" class="text-decoration-none text-reset">{{ $player->name }}</a>
                  @if ($change > 0)
                      <span class="ms-1 text-success">(+{{ round($change) }})</span>
                  @else
                      <span class="ms-1 text-danger">({{ round($change) }})</span>
                  @endif
                </div>
              @endif
            @endforeach
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mb-4">
    <a href="{{ route("players.register") }}" class="btn btn-gradient-dark">Register Player</a>
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


    const container = document.querySelector('.graph-container');
    const svgEl = document.querySelector('#correlation-graph');
    const width = container.clientWidth;
    const height = width; // or height = width * 0.6;

    svgEl.setAttribute("viewBox", `0 0 ${width} ${height}`);
    svgEl.setAttribute("preserveAspectRatio", "xMidYMid meet");

    const svg = d3.select(svgEl);

    const data = {!! json_encode($games) !!};
    const nodes = {};
    const linksMap = new Map();

    for (const game of data) {
        const teams = [
        [game.winner1, game.winner2],
        [game.loser1, game.loser2]
        ];

        for (const [p1, p2] of teams) {
        if (!p1 || !p2) continue;
        const key = [p1.id, p2.id].sort().join('-');
        linksMap.set(key, (linksMap.get(key) || { source: p1.id, target: p2.id, count: 0, players: [p1, p2] }));
        linksMap.get(key).count++;
        // Register nodes
        nodes[p1.id] = p1;
        nodes[p2.id] = p2;
        }
    }

    const linkData = Array.from(linksMap.values());
    const nodeData = Object.values(nodes);

    const simulation = d3.forceSimulation(nodeData)
        .force('link', d3.forceLink(linkData).id(d => d.id).distance(d => 300 / d.count))
        .force('charge', d3.forceManyBody().strength(-50))
        .force('center', d3.forceCenter(width / 2, height / 2));

    const link = svg.append('g')
        .attr('stroke', '#aaa')
        .selectAll('line')
        .data(linkData)
        .join('line')
        .attr('stroke-width', d => Math.sqrt(d.count));

    const node = svg.append('g')
        .selectAll('g')
        .data(nodeData)
        .join('g')
        .call(d3.drag()
        .on('start', dragstarted)
        .on('drag', dragged)
        .on('end', dragended));

    node.append('image')
        .attr('href', d => `https://nanousis.com/foosball/public/storage/${d.avatar}`)
        .attr('width', 40)
        .attr('height', 40)
        .attr('x', -20)
        .attr('y', -20)
        .attr('class','avatar');

    node.append('text')
        .attr('dy', 35)
        .text(d => d.name);

    simulation.on('tick', () => {
        link
        .attr('x1', d => d.source.x)
        .attr('y1', d => d.source.y)
        .attr('x2', d => d.target.x)
        .attr('y2', d => d.target.y);

        node
        .attr('transform', d => `translate(${d.x},${d.y})`);
    });
    simulation.on('tick', () => {
      node
        .attr('transform', d => {
          d.x = Math.max(20, Math.min(width - 50, d.x));
          d.y = Math.max(20, Math.min(height - 50, d.y));
          return `translate(${d.x},${d.y})`;
        });

      link
        .attr('x1', d => d.source.x)
        .attr('y1', d => d.source.y)
        .attr('x2', d => d.target.x)
        .attr('y2', d => d.target.y);
    });
    function dragstarted(event, d) {
        if (!event.active) simulation.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
    }

    function dragged(event, d) {
        d.fx = event.x;
        d.fy = event.y;
    }

    function dragended(event, d) {
        if (!event.active) simulation.alphaTarget(0);
        d.fx = null;
        d.fy = null;
    }

  </script>
  @endsection

