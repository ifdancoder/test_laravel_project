<x-profile :sharedData="$sharedData" doctitle="Who {{$sharedData['name']}} Follows">
    <div class="list-group">
        @foreach($following as $foll)
            <a href="/profile/{{ $foll->followed->username }}" class="list-group-item list-group-item-action">
                <img class="avatar-tiny" src="{{ $foll->followed->avatar }}" alt={{ $foll->followed->username. "'s_avatar" }} />
                {{$foll->followed->username}}
            </a>
        @endforeach
    </div> 
</x-profile>