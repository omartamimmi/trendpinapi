import { useState } from "react";
import { FaPlus, FaTrash, FaEdit } from "react-icons/fa";

interface Group {
  brandName: string;
  description: string;
  position: { lat: number; lng: number };
}

interface Props {
  data: { groups: Group[] };
  onChange: <K extends keyof Props["data"]>(
    field: K,
    value: Props["data"][K]
  ) => void;
}

export default function Step3BrandInformation({ data, onChange }: Props) {
  const [branchType, setBranchType] = useState<"single" | "group">("single");
  const [brandName, setBrandName] = useState("");
  const [description, setDescription] = useState("");
  const [editingIndex, setEditingIndex] = useState<number | null>(null);

  const handleAddBrand = () => {
    if (!brandName.trim()) {
      alert("Please enter a brand name");
      return;
    }

    const newGroup: Group = {
      brandName: brandName.trim(),
      description: description.trim(),
      position: { lat: 31.963158, lng: 35.930359 }, // Default position (Amman, Jordan)
    };

    if (editingIndex !== null) {
      // Update existing brand
      const updated = [...data.groups];
      updated[editingIndex] = newGroup;
      onChange("groups", updated);
      setEditingIndex(null);
    } else {
      // Add new brand
      if (branchType === "single" && data.groups.length > 0) {
        // Replace the single brand
        onChange("groups", [newGroup]);
      } else {
        // Add to group
        onChange("groups", [...data.groups, newGroup]);
      }
    }

    setBrandName("");
    setDescription("");
  };

  const handleEditBrand = (index: number) => {
    const brand = data.groups[index];
    setBrandName(brand.brandName);
    setDescription(brand.description);
    setEditingIndex(index);
  };

  const handleRemoveBrand = (index: number) => {
    const updated = data.groups.filter((_, i) => i !== index);
    onChange("groups", updated);
    if (editingIndex === index) {
      setEditingIndex(null);
      setBrandName("");
      setDescription("");
    }
  };

  const handleBranchTypeChange = (type: "single" | "group") => {
    setBranchType(type);
    if (type === "single" && data.groups.length > 1) {
      // Keep only the first brand when switching to single
      onChange("groups", [data.groups[0]]);
    }
  };

  return (
    <div className="space-y-6">
      <h3 className="text-center text-lg font-semibold text-[#152C5B]">
        Brand Information
      </h3>

      {/* Radio Buttons for Branch Type */}
      <div className="flex justify-center gap-6">
        <div className="flex items-center">
          <input
            type="radio"
            id="single"
            name="branch"
            value="single"
            checked={branchType === "single"}
            onChange={() => handleBranchTypeChange("single")}
            className="w-4 h-4 text-pink-600 bg-gray-100 border-gray-300 focus:ring-pink-500"
          />
          <label htmlFor="single" className="ml-2 text-sm font-medium text-gray-900">
            Single Brand
          </label>
        </div>

        <div className="flex items-center">
          <input
            type="radio"
            id="group"
            name="branch"
            value="group"
            checked={branchType === "group"}
            onChange={() => handleBranchTypeChange("group")}
            className="w-4 h-4 text-pink-600 bg-gray-100 border-gray-300 focus:ring-pink-500"
          />
          <label htmlFor="group" className="ml-2 text-sm font-medium text-gray-900">
            Group Brand
          </label>
        </div>
      </div>

      {/* Add Brand Form */}
      <div className="border rounded-lg p-6 bg-gray-50">
        <h4 className="font-semibold text-gray-800 mb-4">Add Brand</h4>

        <div className="space-y-4">
          <div>
            <label className="block font-medium mb-1">Brand Name *</label>
            <input
              type="text"
              className="w-full border rounded p-3 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
              placeholder="Enter brand name"
              value={brandName}
              onChange={(e) => setBrandName(e.target.value)}
            />
          </div>

          <div>
            <label className="block font-medium mb-1">Description</label>
            <textarea
              className="w-full border rounded p-3 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
              placeholder="Enter brand description"
              rows={3}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
            />
          </div>

          <button
            type="button"
            onClick={handleAddBrand}
            className="flex items-center gap-2 bg-[#E8347E] text-white px-4 py-2 rounded hover:bg-[#d12e6e] transition"
          >
            <FaPlus /> {editingIndex !== null ? 'Update Brand' : (branchType === 'single' && data.groups.length > 0 ? 'Replace Brand' : 'Add Brand')}
          </button>
          {editingIndex !== null && (
            <button
              type="button"
              onClick={() => {
                setEditingIndex(null);
                setBrandName("");
                setDescription("");
              }}
              className="ml-2 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition"
            >
              Cancel
            </button>
          )}
        </div>
      </div>

      {/* Brand List */}
      {data.groups.length > 0 && (
        <div className="space-y-3">
          <h4 className="font-semibold text-gray-800">
            {branchType === 'single' ? 'Your Brand' : `Your Brands (${data.groups.length})`}
          </h4>

          {data.groups.map((group, index) => (
            <div
              key={index}
              className="border rounded-lg bg-white shadow-sm overflow-hidden"
            >
              <div className="bg-[#2F305A] text-white p-3 flex justify-between items-center">
                <span className="font-medium">
                  {branchType === 'single' ? 'Brand Information' : `Shop #${index + 1}`}
                </span>
                <div className="flex gap-3">
                  <button
                    type="button"
                    onClick={() => handleEditBrand(index)}
                    className="text-white hover:text-gray-300 transition"
                    title="Edit brand"
                  >
                    <FaEdit />
                  </button>
                  <button
                    type="button"
                    onClick={() => handleRemoveBrand(index)}
                    className="text-red-300 hover:text-red-500 transition"
                    title="Remove brand"
                  >
                    <FaTrash />
                  </button>
                </div>
              </div>

              <div className="p-4 space-y-2">
                <p className="text-sm">
                  <strong>Name:</strong> {group.brandName}
                </p>
                {group.description && (
                  <p className="text-sm">
                    <strong>Description:</strong> {group.description}
                  </p>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {data.groups.length === 0 && (
        <div className="text-center py-8 text-gray-500">
          <p>No brands added yet. Add your first brand above.</p>
        </div>
      )}
    </div>
  );
}
