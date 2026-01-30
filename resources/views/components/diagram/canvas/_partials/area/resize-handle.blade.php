   <!-- Top -->
   <div
   class="absolute top-0 left-0 right-0 h-2 cursor-ns-resize z-10 resize-handle"
   data-resize="top"
   @mousedown.stop="startResize('top', $event)"
></div>
<!-- Right -->
<div
   class="absolute top-0 right-0 bottom-0 w-2 cursor-ew-resize z-10 resize-handle"
   data-resize="right"
   @mousedown.stop="startResize('right', $event)"
></div>
<!-- Bottom -->
<div
   class="absolute bottom-0 left-0 right-0 h-2 cursor-ns-resize z-10 resize-handle"
   data-resize="bottom"
   @mousedown.stop="startResize('bottom', $event)"
></div>
<!-- Left -->
<div
   class="absolute top-0 left-0 bottom-0 w-2 cursor-ew-resize z-10 resize-handle"
   data-resize="left"
   @mousedown.stop="startResize('left', $event)"
></div>
<!-- Corner Handles -->
<!-- Top-Left -->
<div
   class="absolute top-0 left-0 w-3 h-3 cursor-nwse-resize z-10 resize-handle"
   data-resize="top-left"
   @mousedown.stop="startResize('top-left', $event)"
></div>
<!-- Top-Right -->
<div
   class="absolute top-0 right-0 w-3 h-3 cursor-nesw-resize z-10 resize-handle"
   data-resize="top-right"
   @mousedown.stop="startResize('top-right', $event)"
></div>
<!-- Bottom-Right -->
<div
   class="absolute bottom-0 right-0 w-3 h-3 cursor-nwse-resize z-10 resize-handle"
   data-resize="bottom-right"
   @mousedown.stop="startResize('bottom-right', $event)"
></div>
<!-- Bottom-Left -->
<div
   class="absolute bottom-0 left-0 w-3 h-3 cursor-nesw-resize z-10 resize-handle"
   data-resize="bottom-left"
   @mousedown.stop="startResize('bottom-left', $event)"
></div>
