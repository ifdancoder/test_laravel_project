<x-profile :sharedData="$sharedData" doctitle="{{ $sharedData['name'] }}'s Followers">
    @include('profile_followers_only')
</x-profile>