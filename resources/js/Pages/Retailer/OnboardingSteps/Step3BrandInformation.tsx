import { useState, useEffect } from "react";
import {
  FaPlus,
  FaChevronDown,
  FaChevronUp,
  FaTrash,
  FaEdit,
  FaMapMarkerAlt,
  FaStore,
  FaTimes,
} from "react-icons/fa";

import {
  MapContainer,
  Marker,
  TileLayer,
  useMapEvents,
} from "react-leaflet";

import "leaflet/dist/leaflet.css";
import L from "leaflet";

// --------------------------------------------------------
// Interfaces
// --------------------------------------------------------
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

// --------------------------------------------------------
const createEmptyGroup = (position: { lat: number; lng: number }): Group => ({
  brandName: "",
  description: "",
  position,
});

// --------------------------------------------------------
export default function Step3BrandInformation({ data, onChange }: Props) {
  // Fix Leaflet icons
  useEffect(() => {
    delete (L.Icon.Default.prototype as any)._getIconUrl;
    L.Icon.Default.mergeOptions({
      iconRetinaUrl:
        "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png",
      iconUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png",
      shadowUrl:
        "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",
    });
  }, []);

  // Initialize branchType based on existing data
  const [branchType, setBranchType] = useState<"single" | "group">(() => {
    return data.groups.length > 1 ? "group" : "single";
  });

  const [groupsState, setGroupsState] = useState<
    { id: number; open: boolean }[]
  >([]);

  const [position] = useState<{ lat: number; lng: number }>({
    lat: 31.963158,
    lng: 35.930359,
  });

  const [modalVisible, setModalVisible] = useState(false);
  const [editingIndex, setEditingIndex] = useState<number | null>(null);
  const [tempGroup, setTempGroup] = useState<Group>(createEmptyGroup(position));

  // DELETE MODAL
  const [deleteModal, setDeleteModal] = useState(false);
  const [deleteIndex, setDeleteIndex] = useState<number | null>(null);

  // --------------------------------------------------------
  // LOAD groupsState depending on mode
  // --------------------------------------------------------
  useEffect(() => {
    setGroupsState(
      data.groups.map((_, idx) => ({
        id: idx + 1,
        open: true,
      }))
    );
  }, [data.groups, branchType]);

  // --------------------------------------------------------
  // Modal handlers
  // --------------------------------------------------------
  const openModalFor = (index: number) => {
    setEditingIndex(index);

    if (index === -1) {
      setTempGroup(createEmptyGroup(position));
    } else {
      setTempGroup(data.groups[index]);
    }

    setModalVisible(true);
  };

  const closeModal = () => {
    setEditingIndex(null);
    setModalVisible(false);
  };

  const handleSave = () => {
    let updated = [...data.groups];

    if (editingIndex === -1) {
      updated = [...updated, tempGroup];
    } else if (editingIndex !== null) {
      updated[editingIndex] = tempGroup;
    }

    onChange("groups", updated);

    setModalVisible(false);
    setEditingIndex(null);
  };

  // --------------------------------------------------------
  // Delete handlers
  // --------------------------------------------------------
  const confirmDelete = (index: number) => {
    setDeleteIndex(index);
    setDeleteModal(true);
  };

  const deleteBranch = () => {
    if (deleteIndex !== null) {
      const updated = data.groups.filter((_, i) => i !== deleteIndex);
      onChange("groups", updated);
    }
    setDeleteModal(false);
    setDeleteIndex(null);
  };

  // --------------------------------------------------------
  // Toggle group open/close
  // --------------------------------------------------------
  const toggleGroup = (id: number) => {
    setGroupsState((prev) =>
      prev.map((g) => (g.id === id ? { ...g, open: !g.open } : g))
    );
  };

  // --------------------------------------------------------
  // Map click marker for modal editing
  // --------------------------------------------------------
  const LocationMarker = () => {
    useMapEvents({
      click(e) {
        setTempGroup({
          ...tempGroup,
          position: { lat: e.latlng.lat, lng: e.latlng.lng },
        });
      },
    });
    return null;
  };

  // --------------------------------------------------------
  // EDIT MODAL (Modern Design)
  // --------------------------------------------------------
  const renderModal = () => (
    <>
      {/* Backdrop */}
      <div
        className={`fixed inset-0 bg-black/50 backdrop-blur-sm z-50 transition-opacity duration-300 ${
          modalVisible ? "opacity-100" : "opacity-0 pointer-events-none"
        }`}
        onClick={(e) => {
          e.preventDefault();
          e.stopPropagation();
          closeModal();
        }}
      />

      {/* Modal */}
      <div
        className={`fixed inset-0 z-50 flex items-center justify-center p-4 transition-all duration-300 ${
          modalVisible ? "opacity-100" : "opacity-0 pointer-events-none"
        }`}
      >
        <div
          className={`bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden transform transition-all duration-300 ${
            modalVisible ? "scale-100 translate-y-0" : "scale-95 translate-y-4"
          }`}
          onClick={(e) => e.stopPropagation()}
        >
          {/* Modal Header */}
          <div className="bg-gradient-to-r from-pink-500 to-purple-600 px-6 py-4 flex items-center justify-between">
            <div className="flex items-center space-x-3">
              <div className="p-2 bg-white/20 rounded-lg">
                <FaStore className="w-5 h-5 text-white" />
              </div>
              <div>
                <h3 className="text-xl font-bold text-white">
                  {editingIndex === -1 ? "Add New Brand" : "Edit Brand"}
                </h3>
                <p className="text-white/80 text-sm">
                  Enter brand details and location
                </p>
              </div>
            </div>
            <button
              type="button"
              onClick={closeModal}
              className="p-2 hover:bg-white/20 rounded-lg transition-colors"
            >
              <FaTimes className="w-5 h-5 text-white" />
            </button>
          </div>

          {/* Modal Body */}
          <div className="p-6 space-y-6 overflow-y-auto max-h-[calc(90vh-180px)]">
            {/* Brand Name */}
            <div className="space-y-2">
              <label className="flex items-center text-sm font-medium text-gray-700">
                <FaStore className="w-4 h-4 mr-2 text-gray-400" />
                Brand Name
              </label>
              <input
                type="text"
                value={tempGroup.brandName}
                onChange={(e) =>
                  setTempGroup({ ...tempGroup, brandName: e.target.value })
                }
                placeholder="Enter brand name"
                className="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-200"
              />
            </div>

            {/* Description */}
            <div className="space-y-2">
              <label className="flex items-center text-sm font-medium text-gray-700">
                <svg
                  className="w-4 h-4 mr-2 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M4 6h16M4 12h16M4 18h7"
                  />
                </svg>
                Description
              </label>
              <textarea
                value={tempGroup.description}
                onChange={(e) =>
                  setTempGroup({ ...tempGroup, description: e.target.value })
                }
                placeholder="Enter brand description"
                rows={3}
                className="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all duration-200 resize-none"
              />
            </div>

            {/* Map Location */}
            <div className="space-y-2">
              <label className="flex items-center text-sm font-medium text-gray-700">
                <FaMapMarkerAlt className="w-4 h-4 mr-2 text-gray-400" />
                Location (Click on map to set)
              </label>
              <div className="h-[250px] w-full rounded-xl overflow-hidden border-2 border-gray-200">
                <MapContainer
                  center={tempGroup.position}
                  zoom={13}
                  style={{ height: "250px", width: "100%" }}
                >
                  <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                  <Marker position={tempGroup.position} />
                  <LocationMarker />
                </MapContainer>
              </div>
              <div className="flex items-center space-x-4 mt-2">
                <div className="flex-1 bg-gray-50 rounded-lg px-3 py-2">
                  <span className="text-xs text-gray-500">Latitude</span>
                  <p className="text-sm font-medium text-gray-700">
                    {tempGroup.position.lat.toFixed(6)}
                  </p>
                </div>
                <div className="flex-1 bg-gray-50 rounded-lg px-3 py-2">
                  <span className="text-xs text-gray-500">Longitude</span>
                  <p className="text-sm font-medium text-gray-700">
                    {tempGroup.position.lng.toFixed(6)}
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Modal Footer */}
          <div className="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-200">
            <button
              type="button"
              onClick={closeModal}
              className="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-300 transition-colors"
            >
              Cancel
            </button>
            <button
              type="button"
              onClick={handleSave}
              className="px-6 py-2.5 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-xl font-medium hover:from-pink-600 hover:to-purple-700 transition-all shadow-lg shadow-pink-500/25"
            >
              {editingIndex === -1 ? "Add Brand" : "Save Changes"}
            </button>
          </div>
        </div>
      </div>
    </>
  );

  // --------------------------------------------------------
  // DELETE MODAL (Modern Design)
  // --------------------------------------------------------
  const renderDeleteModal = () => {
    if (deleteIndex === null) return null;

    const item = data.groups[deleteIndex];

    return (
      <>
        {/* Backdrop */}
        <div
          className={`fixed inset-0 bg-black/50 backdrop-blur-sm z-50 transition-opacity duration-300 ${
            deleteModal ? "opacity-100" : "opacity-0 pointer-events-none"
          }`}
          onClick={(e) => {
            e.preventDefault();
            e.stopPropagation();
            setDeleteModal(false);
          }}
        />

        {/* Modal */}
        <div
          className={`fixed inset-0 z-50 flex items-center justify-center p-4 transition-all duration-300 ${
            deleteModal ? "opacity-100" : "opacity-0 pointer-events-none"
          }`}
        >
          <div
            className={`bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 ${
              deleteModal ? "scale-100 translate-y-0" : "scale-95 translate-y-4"
            }`}
            onClick={(e) => e.stopPropagation()}
          >
            {/* Modal Header */}
            <div className="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4 flex items-center justify-between">
              <div className="flex items-center space-x-3">
                <div className="p-2 bg-white/20 rounded-lg">
                  <FaTrash className="w-5 h-5 text-white" />
                </div>
                <div>
                  <h3 className="text-xl font-bold text-white">
                    Confirm Delete
                  </h3>
                  <p className="text-white/80 text-sm">
                    This action cannot be undone
                  </p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setDeleteModal(false)}
                className="p-2 hover:bg-white/20 rounded-lg transition-colors"
              >
                <FaTimes className="w-5 h-5 text-white" />
              </button>
            </div>

            {/* Modal Body */}
            <div className="p-6 space-y-4">
              <div className="bg-red-50 border border-red-200 rounded-xl p-4">
                <p className="text-red-700 font-medium">
                  Are you sure you want to delete this brand?
                </p>
              </div>

              <div className="bg-gray-50 rounded-xl p-4 space-y-3">
                <div className="flex items-center space-x-2">
                  <FaStore className="w-4 h-4 text-gray-400" />
                  <span className="text-sm text-gray-500">Name:</span>
                  <span className="text-sm font-medium text-gray-700">
                    {item.brandName || "Untitled"}
                  </span>
                </div>
                <div className="flex items-start space-x-2">
                  <svg
                    className="w-4 h-4 text-gray-400 mt-0.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth="2"
                      d="M4 6h16M4 12h16M4 18h7"
                    />
                  </svg>
                  <span className="text-sm text-gray-500">Description:</span>
                  <span className="text-sm text-gray-700">
                    {item.description || "No description"}
                  </span>
                </div>
              </div>

              <div className="h-[150px] w-full rounded-xl overflow-hidden border border-gray-200">
                <MapContainer
                  center={item.position}
                  zoom={13}
                  style={{ height: "150px" }}
                >
                  <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                  <Marker position={item.position} />
                </MapContainer>
              </div>
            </div>

            {/* Modal Footer */}
            <div className="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-200">
              <button
                type="button"
                onClick={() => setDeleteModal(false)}
                className="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-300 transition-colors"
              >
                Cancel
              </button>
              <button
                type="button"
                onClick={deleteBranch}
                className="px-6 py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-xl font-medium hover:from-red-600 hover:to-red-700 transition-all shadow-lg shadow-red-500/25"
              >
                Delete Brand
              </button>
            </div>
          </div>
        </div>
      </>
    );
  };

  // --------------------------------------------------------
  // RENDER UI
  // --------------------------------------------------------
  return (
    <div className="space-y-8">
      {renderModal()}
      {renderDeleteModal()}

      {/* Header Section */}
      <div className="text-center">
        <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 mb-4">
          <FaStore className="w-8 h-8 text-white" />
        </div>
        <h3 className="text-2xl font-bold text-gray-800">Brand Information</h3>
        <p className="text-gray-500 mt-2">
          Add your brand details and business locations
        </p>
      </div>

      {/* Brand Type Selection Card */}
      <div className="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div className="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-white rounded-lg shadow-sm">
              <svg
                className="w-5 h-5 text-pink-500"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                />
              </svg>
            </div>
            <div>
              <h4 className="font-semibold text-gray-800">
                Select Brand Type
              </h4>
              <p className="text-sm text-gray-500">
                Choose how your business is structured
              </p>
            </div>
          </div>
        </div>

        <div className="p-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Single Brand Option */}
            <label
              className={`relative flex items-center p-5 rounded-xl border-2 cursor-pointer transition-all duration-200 ${
                branchType === "single"
                  ? "border-pink-500 bg-pink-50 shadow-md"
                  : "border-gray-200 hover:border-pink-300 hover:bg-gray-50"
              }`}
            >
              <input
                type="radio"
                name="branchType"
                value="single"
                checked={branchType === "single"}
                onChange={() => setBranchType("single")}
                className="sr-only"
              />
              <div
                className={`flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center ${
                  branchType === "single" ? "bg-pink-500" : "bg-gray-100"
                }`}
              >
                <FaStore
                  className={`w-6 h-6 ${
                    branchType === "single" ? "text-white" : "text-gray-400"
                  }`}
                />
              </div>
              <div className="ml-4 flex-1">
                <span
                  className={`block font-semibold ${
                    branchType === "single" ? "text-pink-700" : "text-gray-700"
                  }`}
                >
                  Single Brand
                </span>
                <span className="text-sm text-gray-500">
                  One brand with one location
                </span>
              </div>
              {branchType === "single" && (
                <div className="absolute top-3 right-3">
                  <svg
                    className="w-6 h-6 text-pink-500"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fillRule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clipRule="evenodd"
                    />
                  </svg>
                </div>
              )}
            </label>

            {/* Group Brand Option */}
            <label
              className={`relative flex items-center p-5 rounded-xl border-2 cursor-pointer transition-all duration-200 ${
                branchType === "group"
                  ? "border-pink-500 bg-pink-50 shadow-md"
                  : "border-gray-200 hover:border-pink-300 hover:bg-gray-50"
              }`}
            >
              <input
                type="radio"
                name="branchType"
                value="group"
                checked={branchType === "group"}
                onChange={() => setBranchType("group")}
                className="sr-only"
              />
              <div
                className={`flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center ${
                  branchType === "group" ? "bg-pink-500" : "bg-gray-100"
                }`}
              >
                <svg
                  className={`w-6 h-6 ${
                    branchType === "group" ? "text-white" : "text-gray-400"
                  }`}
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  />
                </svg>
              </div>
              <div className="ml-4 flex-1">
                <span
                  className={`block font-semibold ${
                    branchType === "group" ? "text-pink-700" : "text-gray-700"
                  }`}
                >
                  Group Brand
                </span>
                <span className="text-sm text-gray-500">
                  Multiple brands or locations
                </span>
              </div>
              {branchType === "group" && (
                <div className="absolute top-3 right-3">
                  <svg
                    className="w-6 h-6 text-pink-500"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fillRule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clipRule="evenodd"
                    />
                  </svg>
                </div>
              )}
            </label>
          </div>
        </div>
      </div>

      {/* SINGLE MODE */}
      {branchType === "single" && (
        <div className="space-y-4">
          {data.groups.length === 0 && (
            <div className="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
              <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                <FaStore className="w-8 h-8 text-gray-400" />
              </div>
              <h4 className="text-lg font-semibold text-gray-700 mb-2">
                No Brand Added Yet
              </h4>
              <p className="text-gray-500 mb-6">
                Add your brand information to continue
              </p>
              <button
                type="button"
                onClick={() => openModalFor(-1)}
                className="inline-flex items-center px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-xl font-medium hover:from-pink-600 hover:to-purple-700 transition-all shadow-lg shadow-pink-500/25"
              >
                <FaPlus className="w-4 h-4 mr-2" />
                Add Brand
              </button>
            </div>
          )}

          {data.groups.length > 0 && (
            <div className="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
              <div className="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4 flex items-center justify-between">
                <div className="flex items-center space-x-3">
                  <div className="p-2 bg-white/20 rounded-lg">
                    <FaStore className="w-5 h-5 text-white" />
                  </div>
                  <span className="text-white font-semibold">
                    Brand Information
                  </span>
                </div>
                <div className="flex items-center space-x-2">
                  <button
                    type="button"
                    onClick={() => openModalFor(0)}
                    className="p-2 bg-white/20 rounded-lg hover:bg-white/30 transition-colors"
                  >
                    <FaEdit className="w-4 h-4 text-white" />
                  </button>
                  <button
                    type="button"
                    onClick={() => confirmDelete(0)}
                    className="p-2 bg-red-500/80 rounded-lg hover:bg-red-500 transition-colors"
                  >
                    <FaTrash className="w-4 h-4 text-white" />
                  </button>
                </div>
              </div>

              <div className="p-6 space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="bg-gray-50 rounded-xl p-4">
                    <div className="flex items-center space-x-2 mb-1">
                      <FaStore className="w-4 h-4 text-gray-400" />
                      <span className="text-xs text-gray-500 uppercase tracking-wide">
                        Brand Name
                      </span>
                    </div>
                    <p className="text-gray-800 font-medium">
                      {data.groups[0].brandName || "Not specified"}
                    </p>
                  </div>
                  <div className="bg-gray-50 rounded-xl p-4">
                    <div className="flex items-center space-x-2 mb-1">
                      <FaMapMarkerAlt className="w-4 h-4 text-gray-400" />
                      <span className="text-xs text-gray-500 uppercase tracking-wide">
                        Location
                      </span>
                    </div>
                    <p className="text-gray-800 font-medium">
                      {data.groups[0].position.lat.toFixed(4)},{" "}
                      {data.groups[0].position.lng.toFixed(4)}
                    </p>
                  </div>
                </div>

                <div className="bg-gray-50 rounded-xl p-4">
                  <div className="flex items-center space-x-2 mb-1">
                    <svg
                      className="w-4 h-4 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth="2"
                        d="M4 6h16M4 12h16M4 18h7"
                      />
                    </svg>
                    <span className="text-xs text-gray-500 uppercase tracking-wide">
                      Description
                    </span>
                  </div>
                  <p className="text-gray-700">
                    {data.groups[0].description || "No description provided"}
                  </p>
                </div>

                <div className="h-[200px] w-full rounded-xl overflow-hidden border border-gray-200">
                  <MapContainer
                    center={data.groups[0].position}
                    zoom={13}
                    style={{ height: "200px" }}
                  >
                    <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                    <Marker position={data.groups[0].position} />
                  </MapContainer>
                </div>
              </div>
            </div>
          )}
        </div>
      )}

      {/* GROUP MODE */}
      {branchType === "group" && (
        <div className="space-y-4">
          <button
            type="button"
            onClick={() => openModalFor(-1)}
            className="inline-flex items-center px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-600 text-white rounded-xl font-medium hover:from-pink-600 hover:to-purple-700 transition-all shadow-lg shadow-pink-500/25"
          >
            <FaPlus className="w-4 h-4 mr-2" />
            Add Brand
          </button>

          {data.groups.length === 0 && (
            <div className="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 text-center">
              <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                <svg
                  className="w-8 h-8 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  />
                </svg>
              </div>
              <h4 className="text-lg font-semibold text-gray-700 mb-2">
                No Brands Added Yet
              </h4>
              <p className="text-gray-500">
                Click the button above to add your first brand
              </p>
            </div>
          )}

          {data.groups.length > 0 &&
            groupsState.map((g, i) => (
              <div
                key={g.id}
                className="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden"
              >
                <div
                  className="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4 flex items-center justify-between cursor-pointer"
                  onClick={() => toggleGroup(g.id)}
                >
                  <div className="flex items-center space-x-3">
                    <div className="p-2 bg-white/20 rounded-lg">
                      <FaStore className="w-5 h-5 text-white" />
                    </div>
                    <span className="text-white font-semibold">
                      Brand #{i + 1}:{" "}
                      {data.groups[i].brandName || "Untitled"}
                    </span>
                  </div>
                  <div className="flex items-center space-x-2">
                    <button
                      type="button"
                      onClick={(e) => {
                        e.stopPropagation();
                        openModalFor(i);
                      }}
                      className="p-2 bg-white/20 rounded-lg hover:bg-white/30 transition-colors"
                    >
                      <FaEdit className="w-4 h-4 text-white" />
                    </button>
                    <button
                      type="button"
                      onClick={(e) => {
                        e.stopPropagation();
                        confirmDelete(i);
                      }}
                      className="p-2 bg-red-500/80 rounded-lg hover:bg-red-500 transition-colors"
                    >
                      <FaTrash className="w-4 h-4 text-white" />
                    </button>
                    <div className="p-2 bg-white/10 rounded-lg">
                      {g.open ? (
                        <FaChevronUp className="w-4 h-4 text-white" />
                      ) : (
                        <FaChevronDown className="w-4 h-4 text-white" />
                      )}
                    </div>
                  </div>
                </div>

                {g.open && (
                  <div className="p-6 space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div className="bg-gray-50 rounded-xl p-4">
                        <div className="flex items-center space-x-2 mb-1">
                          <FaStore className="w-4 h-4 text-gray-400" />
                          <span className="text-xs text-gray-500 uppercase tracking-wide">
                            Brand Name
                          </span>
                        </div>
                        <p className="text-gray-800 font-medium">
                          {data.groups[i].brandName || "Not specified"}
                        </p>
                      </div>
                      <div className="bg-gray-50 rounded-xl p-4">
                        <div className="flex items-center space-x-2 mb-1">
                          <FaMapMarkerAlt className="w-4 h-4 text-gray-400" />
                          <span className="text-xs text-gray-500 uppercase tracking-wide">
                            Location
                          </span>
                        </div>
                        <p className="text-gray-800 font-medium">
                          {data.groups[i].position.lat.toFixed(4)},{" "}
                          {data.groups[i].position.lng.toFixed(4)}
                        </p>
                      </div>
                    </div>

                    <div className="bg-gray-50 rounded-xl p-4">
                      <div className="flex items-center space-x-2 mb-1">
                        <svg
                          className="w-4 h-4 text-gray-400"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M4 6h16M4 12h16M4 18h7"
                          />
                        </svg>
                        <span className="text-xs text-gray-500 uppercase tracking-wide">
                          Description
                        </span>
                      </div>
                      <p className="text-gray-700">
                        {data.groups[i].description || "No description provided"}
                      </p>
                    </div>

                    <div className="h-[180px] w-full rounded-xl overflow-hidden border border-gray-200">
                      <MapContainer
                        center={data.groups[i].position}
                        zoom={13}
                        style={{ height: "180px" }}
                      >
                        <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
                        <Marker position={data.groups[i].position} />
                      </MapContainer>
                    </div>
                  </div>
                )}
              </div>
            ))}
        </div>
      )}

      {/* Fix Leaflet map z-index to not overlap modal */}
      <style>{`
        .leaflet-container {
          z-index: 1 !important;
        }
        .leaflet-pane {
          z-index: 1 !important;
        }
        .leaflet-top, .leaflet-bottom {
          z-index: 2 !important;
        }
      `}</style>
    </div>
  );
}
