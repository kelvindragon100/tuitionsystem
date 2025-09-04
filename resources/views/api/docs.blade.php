{{-- resources/views/api/docs.blade.php --}}
<x-guest-layout>
    <style>[x-cloak]{display:none!important}</style>

    <div class="mb-5 text-center">
        <h1 class="text-xl font-semibold">API Docs (Protected by Sanctum)</h1>
        <p class="text-sm text-gray-600 mt-1">Use your personal access token to call the endpoints below.</p>
    </div>

    {{-- 令牌生成（开发/演示用途） --}}
    <div class="mb-4 p-3 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-900">
        <div class="flex items-center justify-between gap-3">
            <div class="text-sm">
                <div class="font-medium">Personal Access Token</div>
                <div class="opacity-90">Click to generate a demo token (for the current logged-in user).</div>
            </div>
            <form method="POST" action="{{ route('api.docs.token') }}">
                @csrf
                <x-primary-button>Generate Token</x-primary-button>
            </form>
        </div>
        @if(session('api_token'))
            <div class="mt-3 text-xs break-all">
                <div class="font-medium">Token:</div>
                <code class="bg-white rounded px-2 py-1 inline-block">{{ session('api_token') }}</code>
            </div>
        @endif
    </div>

    {{-- 手动输入 Token --}}
    <div class="mb-4">
        <x-input-label for="token" :value="__('Bearer Token (paste here)')" />
        <x-text-input id="token" class="block mt-1 w-full" type="text" value="{{ session('api_token') }}" />
        <p class="text-xs text-gray-500 mt-1">This token will be used in <code>Authorization: Bearer &lt;token&gt;</code>.</p>
    </div>

    {{-- 试一试：接口操场 --}}
    <div x-data="apiPlayground()" x-cloak class="space-y-8">

        {{-- Users 列表 --}}
        <div class="space-y-3">
            <h2 class="font-semibold">GET /api/v1/users</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                <div>
                    <x-input-label value="role (optional)" />
                    <select x-model="users.role" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">(all)</option>
                        <option value="student">student</option>
                        <option value="tutor">tutor</option>
                        <option value="admin">admin</option>
                    </select>
                </div>
                <div>
                    <x-input-label value="search (optional)" />
                    <x-text-input x-model="users.search" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="per_page" />
                    <x-text-input x-model.number="users.per_page" class="mt-1 w-full" type="number" min="1" max="100" />
                </div>
                <div class="flex items-end">
                    <x-primary-button
                        @click="callUsers()"
                        x-bind:disabled="loading.users"
                        x-bind:class="loading.users ? 'opacity-60 cursor-not-allowed' : ''">
                        <span class="inline-flex items-center gap-2">
                            <svg x-show="loading.users" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                            </svg>
                            <span x-text="loading.users ? 'Loading…' : 'Try it'"></span>
                        </span>
                    </x-primary-button>
                </div>
            </div>

            {{-- 状态横幅 --}}
            <template x-if="status.users.code">
                <div
                    class="p-3 rounded-lg border"
                    x-bind:class="status.users.ok
                        ? 'bg-green-50 border-green-200 text-green-800'
                        : 'bg-red-50 border-red-200 text-red-800'">
                    <div class="flex items-start gap-2">
                        <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                             x-show="status.users.ok">
                            <path d="M12 2a10 10 0 1 1 0 20A10 10 0 0 1 12 2zm-1 11.17l5.59-5.58 1.41 1.41L11 16 6 11l1.41-1.41L11 13.17z"/>
                        </svg>
                        <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                             x-show="!status.users.ok">
                            <path d="M11 2l9 18H2L11 2zm0 6a1 1 0 00-1 1v4a1 1 0 102 0V9a1 1 0 00-1-1zm0 8a1.25 1.25 0 110-2.5 1.25 1.25 0 010 2.5z"/>
                        </svg>
                        <div class="text-sm">
                            <div class="font-medium">
                                <span x-text="'Status ' + status.users.code"></span>
                                <span class="opacity-70" x-text="' • ' + status.users.ms + ' ms'"></span>
                            </div>
                            <div class="opacity-90" x-text="status.users.message"></div>
                        </div>
                    </div>
                </div>
            </template>

            <pre class="mt-1 p-3 bg-gray-900 text-gray-100 rounded overflow-auto text-xs" x-text="usersResult"></pre>
        </div>

        {{-- Subjects 列表 --}}
        <div class="space-y-3">
            <h2 class="font-semibold">GET /api/v1/subjects</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                <div>
                    <x-input-label value="q (optional)" />
                    <x-text-input x-model="subjects.q" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label value="per_page" />
                    <x-text-input x-model.number="subjects.per_page" class="mt-1 w-full" type="number" min="1" max="100" />
                </div>
                <div class="flex items-end">
                    <x-primary-button
                        @click="callSubjects()"
                        x-bind:disabled="loading.subjects"
                        x-bind:class="loading.subjects ? 'opacity-60 cursor-not-allowed' : ''">
                        <span class="inline-flex items-center gap-2">
                            <svg x-show="loading.subjects" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                            </svg>
                            <span x-text="loading.subjects ? 'Loading…' : 'Try it'"></span>
                        </span>
                    </x-primary-button>
                </div>
            </div>

            <template x-if="status.subjects.code">
                <div
                    class="p-3 rounded-lg border"
                    x-bind:class="status.subjects.ok
                        ? 'bg-green-50 border-green-200 text-green-800'
                        : 'bg-red-50 border-red-200 text-red-800'">
                    <div class="flex items-start gap-2">
                        <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                             x-show="status.subjects.ok">
                            <path d="M12 2a10 10 0 1 1 0 20A10 10 0 0 1 12 2zm-1 11.17l5.59-5.58 1.41 1.41L11 16 6 11l1.41-1.41L11 13.17z"/>
                        </svg>
                        <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                             x-show="!status.subjects.ok">
                            <path d="M11 2l9 18H2L11 2zm0 6a1 1 0 00-1 1v4a1 1 0 102 0V9a1 1 0 00-1-1zm0 8a1.25 1.25 0 110-2.5 1.25 1.25 0 010 2.5z"/>
                        </svg>
                        <div class="text-sm">
                            <div class="font-medium">
                                <span x-text="'Status ' + status.subjects.code"></span>
                                <span class="opacity-70" x-text="' • ' + status.subjects.ms + ' ms'"></span>
                            </div>
                            <div class="opacity-90" x-text="status.subjects.message"></div>
                        </div>
                    </div>
                </div>
            </template>

            <pre class="mt-1 p-3 bg-gray-900 text-gray-100 rounded overflow-auto text-xs" x-text="subjectsResult"></pre>
        </div>

        {{-- User 详情 --}}
        <div class="space-y-3">
            <h2 class="font-semibold">GET /api/v1/users/{id}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                <div>
                    <x-input-label value="id" />
                    <x-text-input x-model.number="userId" class="mt-1 w-full" type="number" min="1" />
                </div>
                <div class="flex items-end">
                    <x-primary-button
                        @click="callUserShow()"
                        x-bind:disabled="loading.userShow"
                        x-bind:class="loading.userShow ? 'opacity-60 cursor-not-allowed' : ''">
                        <span class="inline-flex items-center gap-2">
                            <svg x-show="loading.userShow" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                            </svg>
                            <span x-text="loading.userShow ? 'Loading…' : 'Try it'"></span>
                        </span>
                    </x-primary-button>
                </div>
            </div>

            <template x-if="status.userShow.code">
                <div
                    class="p-3 rounded-lg border"
                    x-bind:class="status.userShow.ok
                        ? 'bg-green-50 border-green-200 text-green-800'
                        : 'bg-red-50 border-red-200 text-red-800'">
                    <div class="flex items-start gap-2">
                        <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                             x-show="status.userShow.ok">
                            <path d="M12 2a10 10 0 1 1 0 20A10 10 0 0 1 12 2zm-1 11.17l5.59-5.58 1.41 1.41L11 16 6 11l1.41-1.41L11 13.17z"/>
                        </svg>
                        <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                             x-show="!status.userShow.ok">
                            <path d="M11 2l9 18H2L11 2zm0 6a1 1 0 00-1 1v4a1 1 0 102 0V9a1 1 0 00-1-1zm0 8a1.25 1.25 0 110-2.5 1.25 1.25 0 010 2.5z"/>
                        </svg>
                        <div class="text-sm">
                            <div class="font-medium">
                                <span x-text="'Status ' + status.userShow.code"></span>
                                <span class="opacity-70" x-text="' • ' + status.userShow.ms + ' ms'"></span>
                            </div>
                            <div class="opacity-90" x-text="status.userShow.message"></div>
                        </div>
                    </div>
                </div>
            </template>

            <pre class="mt-1 p-3 bg-gray-900 text-gray-100 rounded overflow-auto text-xs" x-text="userShowResult"></pre>
        </div>

        {{-- Subject 详情 --}}
        <div class="space-y-3">
            <h2 class="font-semibold">GET /api/v1/subjects/{id}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                <div>
                    <x-input-label value="id (subject_id or numeric id)" />
                    <x-text-input x-model="subjectId" class="mt-1 w-full" />
                </div>
                <div class="flex items-end">
                    <x-primary-button
                        @click="callSubjectShow()"
                        x-bind:disabled="loading.subjectShow"
                        x-bind:class="loading.subjectShow ? 'opacity-60 cursor-not-allowed' : ''">
                        <span class="inline-flex items-center gap-2">
                            <svg x-show="loading.subjectShow" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity=".25"></circle>
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4"></path>
                            </svg>
                            <span x-text="loading.subjectShow ? 'Loading…' : 'Try it'"></span>
                        </span>
                    </x-primary-button>
                </div>
            </div>

            <template x-if="status.subjectShow.code">
                <div
                    class="p-3 rounded-lg border"
                    x-bind:class="status.subjectShow.ok
                        ? 'bg-green-50 border-green-200 text-green-800'
                        : 'bg-red-50 border-red-200 text-red-800'">
                    <div class="flex items-start gap-2">
                        <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                             x-show="status.subjectShow.ok">
                            <path d="M12 2a10 10 0 1 1 0 20A10 10 0 0 1 12 2zm-1 11.17l5.59-5.58 1.41 1.41L11 16 6 11l1.41-1.41L11 13.17z"/>
                        </svg>
                        <svg class="h-5 w-5 mt-0.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                             x-show="!status.subjectShow.ok">
                            <path d="M11 2l9 18H2L11 2zm0 6a1 1 0 00-1 1v4a1 1 0 102 0V9a1 1 0 00-1-1zm0 8a1.25 1.25 0 110-2.5 1.25 1.25 0 010 2.5z"/>
                        </svg>
                        <div class="text-sm">
                            <div class="font-medium">
                                <span x-text="'Status ' + status.subjectShow.code"></span>
                                <span class="opacity-70" x-text="' • ' + status.subjectShow.ms + ' ms'"></span>
                            </div>
                            <div class="opacity-90" x-text="status.subjectShow.message"></div>
                        </div>
                    </div>
                </div>
            </template>

            <pre class="mt-1 p-3 bg-gray-900 text-gray-100 rounded overflow-auto text-xs" x-text="subjectShowResult"></pre>
        </div>
    </div>

    <script>
    function apiPlayground(){
        return {
            users: { role: '', search: '', per_page: 10 },
            subjects: { q: '', per_page: 10 },
            userId: 1,
            subjectId: '',
            usersResult: '',
            subjectsResult: '',
            userShowResult: '',
            subjectShowResult: '',
            loading: { users:false, subjects:false, userShow:false, subjectShow:false },
            status: {
                users: { code:null, ok:false, ms:0, message:'' },
                subjects: { code:null, ok:false, ms:0, message:'' },
                userShow: { code:null, ok:false, ms:0, message:'' },
                subjectShow: { code:null, ok:false, ms:0, message:'' },
            },
            tokenEl(){ return document.getElementById('token'); },

            async callUsers(){
                const params = new URLSearchParams();
                if(this.users.role) params.set('role', this.users.role);
                if(this.users.search) params.set('search', this.users.search);
                params.set('per_page', this.users.per_page || 10);
                await this._fetch(`/api/v1/users?${params.toString()}`, 'users', txt => this.usersResult = txt);
            },
            async callSubjects(){
                const params = new URLSearchParams();
                if(this.subjects.q) params.set('q', this.subjects.q);
                params.set('per_page', this.subjects.per_page || 10);
                await this._fetch(`/api/v1/subjects?${params.toString()}`, 'subjects', txt => this.subjectsResult = txt);
            },
            async callUserShow(){
                await this._fetch(`/api/v1/users/${this.userId}`, 'userShow', txt => this.userShowResult = txt);
            },
            async callSubjectShow(){
                await this._fetch(`/api/v1/subjects/${encodeURIComponent(this.subjectId)}`, 'subjectShow', txt => this.subjectShowResult = txt);
            },

            async _fetch(url, key, sink){
                const token = this.tokenEl()?.value?.trim() || '';
                try{
                    this.loading[key] = true;
                    const t0 = performance.now();
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            ...(token ? {'Authorization': `Bearer ${token}`} : {})
                        }
                    });
                    const text = await res.text();
                    const ms = Math.max(1, Math.round(performance.now() - t0));
                    this.status[key] = {
                        code: res.status,
                        ok: res.ok,
                        ms,
                        message: res.ok ? 'Success' : (res.status === 401 ? 'Unauthorized (check your token)' :
                                  res.status === 403 ? 'Forbidden' :
                                  res.status === 404 ? 'Not Found' :
                                  res.status === 429 ? 'Too Many Requests (throttled)' :
                                  res.status === 500 ? 'Server Error' : res.statusText)
                    };
                    sink(`${res.status} ${res.statusText}\n\n${text}`);
                }catch(err){
                    this.status[key] = { code: 0, ok: false, ms: 0, message: String(err) };
                    sink(`ERROR: ${err}`);
                }finally{
                    this.loading[key] = false;
                }
            }
        }
    }
    </script>
</x-guest-layout>
