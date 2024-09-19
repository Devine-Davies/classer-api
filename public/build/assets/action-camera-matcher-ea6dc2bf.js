let h={0:0,1:2,2:1,3:2,4:0,5:0,6:0,7:0,8:2,9:0};const A=(e,n)=>h[e]=n,S=e=>h[e],E=e=>S(e)!==void 0,j=()=>{let e=0;const n=document.querySelectorAll("[data-question-block-idx]"),c=document.querySelectorAll('[id^="form-question-block-"]'),t=document.querySelector("[data-results]"),l=document.querySelector("[data-classer-billboard]"),p=document.querySelector("[data-submit]"),g=document.querySelector("[data-reset]"),a=document.querySelectorAll("[data-next-question]"),d=document.querySelectorAll("[data-previous-question]"),i=document.querySelectorAll("input[type=radio]"),v=Object.entries(questionnaire.weights),y=x(v,h);console.log(y),[...a,...d].forEach(s=>s.addEventListener("click",o=>{o.preventDefault();const r=s.getAttribute("data-next-question")!==null,f=r?e+1:e-1;if(r&&!E(e)){alert("Please select an answer before proceeding");return}u(f)})),p.addEventListener("click",s=>{s.preventDefault();const o=Object.entries(questionnaire.weights),r=x(o,h);m(r),B(h)}),i.forEach(s=>s.addEventListener("change",o=>{const r=parseInt(o.target.value);A(e,r)})),g.addEventListener("click",s=>{s.preventDefault(),h={},e=0,n.forEach(o=>o.classList.add("hidden")),c[e].classList.remove("hidden"),t.classList.add("hidden"),l.classList.add("hidden"),document.querySelectorAll("input[type=radio]").forEach(o=>{o.checked=!1})});const u=s=>{e=s,n.forEach((o,r)=>r===s?o.classList.remove("hidden"):o.classList.add("hidden"))},m=s=>{const o=Array.from({length:s.length});t.querySelector("ul").innerHTML=o.map((r,f)=>C(f)).join(""),setTimeout(()=>{t.querySelector("ul").innerHTML=s.map(k).join("");const r=t.querySelectorAll(".benefits-list");t.querySelectorAll("[data-toggle-open]").forEach(f=>{f.addEventListener("click",q=>{const L=q.target.getAttribute("data-toggle-open");r.forEach((w,b)=>b===parseInt(L)?w.classList.toggle("hidden"):w.classList.add("hidden"))})})},Math.floor(Math.random()*1e3)+1400),l.classList.remove("hidden"),t.classList.remove("hidden"),l.classList.remove("hidden"),n.forEach(r=>r.classList.add("hidden"))};c[e].classList.remove("hidden")};window.addEventListener("load",j);const x=(e,n)=>{const c=e.reduce((a,[d,i])=>{const y=i.map((u,m)=>u[n[m]]).reduce((u,m)=>m=="out"?u:u+m,0);return{...a,[d]:y}},{}),t=Object.entries(c),l=t.map(([,a])=>a),p=Math.max(...l),g=Math.min(...l);return t.map(([a,d])=>{const i=p!==g?(d-g)/(p-g)*100:0;return{key:a,value:d,percentage:i,recommendationKey:$(i),recommendation:R(i),color:M(i)}})},B=e=>{fetch("https://classermedia.com/api/site/actions-camera-matcher",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({answers:e})}).then(t=>t.json()).then(t=>{console.log("Success:",t)}).catch(t=>{console.error("Error:",t)})},M=e=>e>80?"green":e>60?"orange":e>40?"yellow":"red",$=e=>e>80?"highly-recommended":e>60?"good-match":e>40?"might-like":"not-recommended",R=e=>e>80?"Highly recommended!":e>60?"It's a good match!":e>40?"You can might like it!":"Not recommended!",T=e=>`
    <button data-toggle-open="${e}" class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-500 pointer-events-none"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>`,O=({key:e,recommendation:n})=>`<div class="flex-1 min-w-0">
        <p class="text-md font-bold text-gray-700 truncate dark:text-white">
            ${e}
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            ${n}
        </p>
    </div>`,I=()=>`<ul class="benefits-list hidden grid grid-cols-2">
        ${["Individual configuration","No setup, or hidden fees","Team size: 1 developer","Premium support: 6 months"].map(n=>`
            <li class="flex items-center space-x-3 rtl:space-x-reverse">
                <svg class="flex-shrink-0 w-3.5 h-3.5 text-green-500 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                </svg>
                <span>${n}</span>
            </li>
        `).join("")}
    </ul>`,k=(e,n,c=!0)=>`<li class="recommendation-item ${e.recommendationKey}">
    <div class="flex items-center space-x-4">
    <div class="indicator"></div>
        ${O(e)}
        ${c?T(n):""}
    </div>
    ${I()}
</li>`,C=e=>k({key:"Loading...",recommendation:"Just a moment",recommendationKey:"highly-recommended",color:"green"},e,!1);
