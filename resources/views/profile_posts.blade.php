<x-profile :sharedData="$sharedData" doctitle="{{$sharedData['name']}}'s Profile">
    @include('profile_posts_only')
</x-profile>