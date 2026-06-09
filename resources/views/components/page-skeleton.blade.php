{{-- Generic page skeleton — ipapakita habang naglo-load ang content --}}
<div id="pageSkeletonWrapper">

    {{-- Stats row (4 metric cards) --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
        @for($i = 0; $i < 4; $i++)
        <div class="sk-card">
            <div class="skeleton" style="height:12px; width:60%; margin-bottom:12px;"></div>
            <div class="skeleton" style="height:28px; width:40%;"></div>
        </div>
        @endfor
    </div>

    {{-- Main table/content card --}}
    <div class="sk-card">
        {{-- Table header --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div class="skeleton" style="height:20px; width:180px;"></div>
            <div class="skeleton" style="height:34px; width:100px; border-radius:8px;"></div>
        </div>

        {{-- Table rows --}}
        @for($i = 0; $i < 6; $i++)
        <div style="display:flex; gap:16px; align-items:center; padding:12px 0; border-bottom:1px solid var(--border-default);">
            <div class="skeleton" style="width:32px; height:32px; border-radius:50%; flex-shrink:0;"></div>
            <div class="skeleton" style="height:12px; flex:2;"></div>
            <div class="skeleton" style="height:12px; flex:1;"></div>
            <div class="skeleton" style="height:12px; flex:1;"></div>
            <div class="skeleton" style="height:24px; width:60px; border-radius:999px;"></div>
        </div>
        @endfor
    </div>
</div>