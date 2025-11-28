import { useState, useEffect } from "react";
import { InputText } from "primereact/inputtext";
import { InputTextarea } from "primereact/inputtextarea";
import { RadioButton } from "primereact/radiobutton";
import { Dialog } from "primereact/dialog";

import {
  FaPlus,
  FaChevronDown,
  FaChevronUp,
  FaTrash,
  FaEdit,
} from "react-icons/fa";

import {
  MapContainer,
  Marker,
  TileLayer,
  useMapEvents,
} from "react-leaflet";

import "leaflet/dist/leaflet.css";
import L, { LatLngExpression } from "leaflet";

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

  const [branchType, setBranchType] = useState<"single" | "group">("single");

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

  // NEW: DELETE MODAL
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
    } else {
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
  // EDIT MODAL
  // --------------------------------------------------------
  const renderModal = () => (
    <Dialog
      header="Brand Information"
      visible={modalVisible}
      style={{ width: "50vw" }}
      modal
      onHide={closeModal}
    >
      <div className="space-y-4">
        <div>
     <label htmlFor="brandName"> brandName</label>
        <InputText
          className="w-full"
          value={tempGroup.brandName}
          onChange={(e) =>
            setTempGroup({ ...tempGroup, brandName: e.target.value })
          }
        />
        </div>
            <div>
 <InputTextarea
          className="w-full"
          value={tempGroup.description}
          onChange={(e) =>
            setTempGroup({ ...tempGroup, description: e.target.value })
          }
        />
            </div>

       

        <div className="h-[200px] w-full">
          <MapContainer
            center={tempGroup.position}
            zoom={13}
            style={{ height: "200px" }}
          >
            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
            <Marker position={tempGroup.position} />
            <LocationMarker />
          </MapContainer>
        </div>
          <div className="flex space-x-4">
            <button
          className=" w-1 bg-[#E8347E] text-white py-2 rounded mt-4"
          onClick={handleSave}
        >
          Save
        </button>

         <button
          className=" bg-gray-500 text-white py-2 rounded mt-4"
          onClick={handleSave}
        >
          Cansel
        </button>
          </div>
      
      </div>
    </Dialog>
  );

  // --------------------------------------------------------
  // DELETE MODAL (NEW)
  // --------------------------------------------------------
  const renderDeleteModal = () => {
    if (deleteIndex === null) return null;

    const item = data.groups[deleteIndex];

    return (
      <Dialog
        header="Confirm Delete"
        visible={deleteModal}
        style={{ width: "35vw" }}
        modal
        onHide={() => setDeleteModal(false)}
      >
        <p className="text-lg font-semibold text-red-600 mb-4">
          Are you sure you want to delete this branch?
        </p>

        <p>
          <strong>Name:</strong> {item.brandName || "-"}
        </p>
        <p>
          <strong>Description:</strong> {item.description || "-"}
        </p>

        <div className="h-[200px] w-full rounded overflow-hidden border my-4">
          <MapContainer
            center={item.position}
            zoom={13}
            style={{ height: "200px" }}
          >
            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
            <Marker position={item.position} />
          </MapContainer>
        </div>

        <div className="flex justify-end gap-3 mt-6">
          <button
            className="px-4 py-2 bg-gray-300 rounded"
            onClick={() => setDeleteModal(false)}
          >
            Cancel
          </button>

          <button
            className="px-4 py-2 bg-red-600 text-white rounded"
            onClick={deleteBranch}
          >
            Delete
          </button>
        </div>
      </Dialog>
    );
  };

  // --------------------------------------------------------
  // RENDER UI
  // --------------------------------------------------------
  return (
    <div className="space-y-6">

      {renderModal()}
      {renderDeleteModal()}

      <h3 className="text-center text-lg font-semibold text-[#152C5B]">
        Brand Information
      </h3>

      {/* Radio */}
      <div className="flex justify-center gap-6">
        <RadioButton
          inputId="single"
          name="branch"
          value="single"
          onChange={(e) => setBranchType(e.value)}
          checked={branchType === "single"}
        />
        <label className="ml-2">Single Brand</label>

        <RadioButton
          inputId="group"
          name="branch"
          value="group"
          onChange={(e) => setBranchType(e.value)}
          checked={branchType === "group"}
        />
        <label className="ml-2">Group Brand</label>
      </div>

      {/* ----------------------------------------------------- */}
      {/* SINGLE MODE */}
      {/* ----------------------------------------------------- */}
      {branchType === "single" && (
        <div className="space-y-4">

          {data.groups.length === 0 && (
            <button
              type="button"
              onClick={() => openModalFor(-1)}
              className="flex items-center gap-2 bg-[#E8347E] text-white px-4 py-2 rounded"
            >
              <FaPlus /> Add Brand
            </button>
          )}

          {data.groups.length > 0 && (
            <div className="rounded border shadow bg-white">
              <div className="flex justify-between p-3 bg-[#2F305A] text-white">
                <span>Brand Information</span>

                <div className="flex gap-3 items-center">
                  <FaEdit
                    onClick={() => openModalFor(0)}
                    className="cursor-pointer"
                  />

                  <FaTrash
                    onClick={() => confirmDelete(0)}
                    className="cursor-pointer text-red-300 hover:text-red-500"
                  />
                </div>
              </div>

              <div className="p-3 text-gray-700 text-sm space-y-4">
                <p>
                  <strong>Name:</strong> {data.groups[0].brandName || "-"}
                </p>
                <p>
                  <strong>Description:</strong> {data.groups[0].description || "-"}
                </p>

                <div className="h-[180px] w-full rounded overflow-hidden border">
                  <MapContainer
                    center={data.groups[0].position}
                    zoom={13}
                    style={{ height: "180px" }}
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

      {/* ----------------------------------------------------- */}
      {/* GROUP MODE */}
      {/* ----------------------------------------------------- */}
      {branchType === "group" && (
        <div className="space-y-4">

          <button
            onClick={() => openModalFor(-1)}
            className="flex items-center gap-2 bg-pink-600 text-white px-4 py-2 rounded"
          >
            <FaPlus /> Add Group
          </button>

          {data.groups.length > 0 &&
            groupsState.map((g, i) => (
              <div key={g.id} className="rounded border shadow bg-white">
                <div
                  className="flex justify-between p-3 bg-[#2F305A] text-white cursor-pointer"
                  onClick={() => toggleGroup(g.id)}
                >
                  <span>Shop #{i + 1}</span>

                  <div className="flex gap-3 items-centered">
                    <FaEdit
                      onClick={(e) => {
                        e.stopPropagation();
                        openModalFor(i);
                      }}
                      className="cursor-pointer"
                    />

                    {g.open ? <FaChevronUp /> : <FaChevronDown />}

                    <FaTrash
                      onClick={(e) => {
                        e.stopPropagation();
                        confirmDelete(i);
                      }}
                      className="cursor-pointer text-red-300 hover:text-red-500"
                    />
                  </div>
                </div>

                {g.open && (
                  <div className="p-3 text-gray-700 text-sm space-y-4">
                    <p>
                      <strong>Name:</strong> {data.groups[i].brandName || "-"}
                    </p>
                    <p><strong>Description:</strong> {data.groups[i].description || "-"}</p>

                    <div className="h-[180px] w-full rounded overflow-hidden border">
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
    </div>
  );
}
