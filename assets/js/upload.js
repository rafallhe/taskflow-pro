(() => {
 const form=document.getElementById('dropUpload');
 if(!form)return;
 const zone=document.getElementById('dropZone');
 const input=document.getElementById('attachmentInput');
 const label=document.getElementById('selectedFile');
 const show=()=>{label.textContent=input.files?.[0]?.name||'No file selected';};
 input.addEventListener('change',show);
 ['dragenter','dragover'].forEach(type=>zone.addEventListener(type,e=>{e.preventDefault();zone.classList.add('drag-active')}));
 ['dragleave','drop'].forEach(type=>zone.addEventListener(type,e=>{e.preventDefault();zone.classList.remove('drag-active')}));
 zone.addEventListener('drop',e=>{
  if(!e.dataTransfer.files.length)return;
  const dt=new DataTransfer();
  dt.items.add(e.dataTransfer.files[0]);
  input.files=dt.files;
  show();
 });
})();