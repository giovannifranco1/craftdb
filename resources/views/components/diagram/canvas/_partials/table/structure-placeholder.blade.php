<table class="w-full table-fixed">
    <colgroup>
        <col :style="`width: ${colWidths[0]}px`">
        <col :style="`width: ${colWidths[1]}px`">
        <col style="width: 10%">
    </colgroup>

    <tbody class="divide-y divide-gray-100 dark:divide-zinc-700">
        <tr :style="`height: ${getGridSize()}px;`">
            <td class="py-[2px] px-3">
                <flux:skeleton.line class="w-[70%]"/>
            </td>
            <td>
                <flux:skeleton.line class="w-[60%]"/>
            </td>
            <td>
                <flux:skeleton.line class="w-[80%]"/>
            </td>
        </tr>
        <tr :style="`height: ${getGridSize()}px;`">
            <td class="py-[2px] px-3">
                <flux:skeleton.line class="w-[60%]"/>
            </td>
            <td>
                <flux:skeleton.line class="w-[50%]"/>
            </td>
            <td>
                <flux:skeleton.line class="w-[80%]"/>
            </td>
        </tr>
        <tr :style="`height: ${getGridSize()}px;`">
            <td class="py-[2px] px-3">
                <flux:skeleton.line class="w-[80%]"/>
            </td>
            <td>
                <flux:skeleton.line class="w-[70%]"/>
            </td>
            <td>
                <flux:skeleton.line class="w-[80%]"/>
            </td>
        </tr>
    </tbody>
</table>
