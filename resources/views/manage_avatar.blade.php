<x-layout doctitle="Manage Your Avatar">
    <div class="container py-md-5 container--narrow">
        <h2 class="text-center mb-3">Upload a new Avatar</h2>
        <form action="/manage-avatar" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <input type="file" name="avatar" required>
                @error('avatar')
                <p class="alert alert-danger shadow-sm">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</x-layout>