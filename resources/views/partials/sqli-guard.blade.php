{{-- 复用：SQL Injection 可视化守卫（Alpine data） --}}
<script>
function sqliGuard(){
    const patterns = [
        { re: /('|%27)\s*or\s+('|%27)?\s*1\s*=\s*1/i, tag: "OR 1=1" },
        { re: /('|%27)\s*--/i,                         tag: "Inline comment --" },
        { re: /;\s*drop\s+table/i,                     tag: "DROP TABLE" },
        { re: /union\s+select/i,                       tag: "UNION SELECT" },
        { re: /information_schema/i,                   tag: "information_schema" },
        { re: /sleep\(\s*\d+\s*\)/i,                   tag: "time-based SLEEP()" },
        { re: /;.*(update|delete|insert)\b/i,          tag: "chained DML" },
    ];
    return {
        // 可在页面用 :value / old() 预填充 email；这里默认空
        email: '',
        password: '',
        passwordConfirmation: '',
        sqliTriggered: false,
        lastField: null,
        hits: [],
        scan(field){
            const val = (field==='email' ? this.email :
                        field==='password' ? this.password : this.passwordConfirmation) || '';
            const found = [];
            for (const p of patterns){ if (p.re.test(val)) found.push(p.tag); }
            if (found.length){
                this.sqliTriggered = true;
                this.lastField = field;
                this.hits = Array.from(new Set(found));
            } else {
                // 检查其他字段是否仍命中
                const vals = [this.email, this.password, this.passwordConfirmation].filter(v => v !== val);
                const still = patterns.some(p => vals.some(v => p.re.test(v || '')));
                this.sqliTriggered = still;
                if (!still){ this.lastField = null; this.hits = []; }
            }
        },
        handleSubmit(e){
            this.scan('email'); this.scan('password'); this.scan('password_confirmation');
            if (this.sqliTriggered){
                if (this.lastField){
                    requestAnimationFrame(() => {
                        const target = document.getElementById(this.lastField);
                        if (target){
                            target.classList.remove('shake'); void target.offsetWidth; target.classList.add('shake');
                        }
                    });
                }
                return false;
            }
            e.target.submit();
        }
    }
}
</script>
