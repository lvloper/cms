// Clear Format Extension for Filament TipTap Editor
// This extension adds a "Clear Format" button that removes all formatting from selected text

const ClearFormat = {
    name: 'clearFormat',
    
    // Command to clear formatting
    commands: {
        clearFormat: () => ({ commands, state }) => {
            const { from, to } = state.selection;
            
            // If no selection, clear stored marks
            if (from === to) {
                return commands.unsetAllMarks();
            }
            
            // Clear all marks from selection
            commands.unsetAllMarks();
            
            // Get all mark types and remove them
            const markTypes = Object.keys(state.schema.marks);
            markTypes.forEach(markType => {
                commands.unsetMark(markType);
            });
            
            return true;
        }
    },
    
    // Keyboard shortcuts
    keyboardShortcuts: {
        'Mod-r': () => this.commands.clearFormat(),
        'Mod-\\': () => this.commands.clearFormat(),
    }
};

export default ClearFormat;
