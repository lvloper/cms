<div class="grid gap-4">
    <label for="select" class="text-primary text-xl md:text-3xl font-heavy">{{ $label }}</label>
    <select id="select"
        class="border-[3px] border-primary p-2 sm:p-3 focus:outline-none focus:ring-2 text-md sm:text-xl xl:text-2xl focus:ring-blue-500 text-primary">
        <option value="" disabled selected class="text-primary">{{ $placeholder }}</option>
        <option value="opcion1">Opción 1</option>
        <option value="opcion2">Opción 2</option>
        <option value="opcion3">Opción 3</option>
    </select>
</div>
