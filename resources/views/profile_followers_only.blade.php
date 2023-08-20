<div class="list-group">
    @foreach($followers as $follower)
        <a href="/profile/{{ $follower->following->username }}" class="list-group-item list-group-item-action">
            <img class="avatar-tiny" src="{{ $follower->following->avatar }}" alt={{ $follower->following->username. "'s_avatar" }} />
            {{$follower->following->username}}
        </a>
    @endforeach
</div> 