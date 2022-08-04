<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-flash-message status="session('status')" />
                    <div class="flex justify-end mb-4">
                        <button onclick="location.href='{{ route('owner.products.create') }}'" class="text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg">新規登録する</button>
                    </div>

                    <!-- 画像の一覧表示 -->
                    <div class="flex flex-wrap">
                    @foreach($products as $product) 
                    <!-- 
                        Editの場合、URLが /owner/shops/edit/{shop} となっており、shopのidをURLに渡す必要があるので、
                        routeの第二引数にshopのidをパラメータとして渡す必要がある。　['shop'(キーはURLの{shop}の部分) => $shop->id (現在の$shopの中のidを取ってくる)]
                    -->
                    <div class="w-1/4 p-2 md:p-4">
                        <a href="{{ route('owner.products.edit', ['product' => $product->id]) }}">
                            <div class="border rounded-md p-4">
                                <x-thumbnail :filename="$product->imageFirst->filename" type="products" />
                                    {{-- <divclass="text-gray-700">$product->name }}</div>  --}}
                                </div>

                        </a>
                    </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>