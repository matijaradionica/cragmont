<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class TopoUpload extends Component
{
    use WithFileUploads;

    public $topo;
    public $existingTopoUrl;
    public $removeExisting = false;

    /**
     * Initialize component with existing topo if editing.
     */
    public function mount($existingTopoUrl = null)
    {
        $this->existingTopoUrl = $existingTopoUrl;
    }

    /**
     * Remove the uploaded topo.
     */
    public function removeTopo()
    {
        $this->topo = null;
    }

    /**
     * Mark existing topo for removal.
     */
    public function removeExistingTopo()
    {
        $this->removeExisting = true;
        $this->existingTopoUrl = null;
    }

    /**
     * Cancel removal of existing topo.
     */
    public function cancelRemove()
    {
        $this->removeExisting = false;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.topo-upload');
    }
}
